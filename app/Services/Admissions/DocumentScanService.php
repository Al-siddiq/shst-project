<?php

namespace App\Services\Admissions;

use App\Entities\TenantContext;
use App\Models\Tenant\Admissions\ApplicationDocumentModel;
use App\Models\TenantModel;
use App\Services\Files\MalwareScannerInterface;
use CodeIgniter\I18n\Time;
use RuntimeException;
use Throwable;

/** Claims quarantined uploads, scans them, and promotes only clean objects. */
class DocumentScanService
{
    public function __construct(private readonly MalwareScannerInterface $scanner) {}

    public function processBatch(string $workerId, int $limit = 20): array
    {
        $counts = ['clean' => 0, 'infected' => 0, 'unavailable' => 0];
        for ($i = 0; $i < max(1, min(100, $limit)); $i++) {
            $document = $this->claim($workerId);
            if ($document === null) break;
            $result = $this->process($document, $workerId);
            $counts[$result]++;
        }
        service('tenantContextManager')->clear();
        return $counts;
    }

    public function cleanupRejected(int $olderThanDays=30): int
    {
        $db=db_connect(); $cutoff=date('Y-m-d H:i:s',time()-max(1,$olderThanDays)*86400);
        $rows=$db->table('application_documents')->where('storage_state','rejected')->where('scanned_at <',$cutoff)->limit(500)->get()->getResultArray(); $count=0;
        foreach($rows as $document){
            try{service('applicantDocumentStorage')->discard($document);}catch(Throwable){continue;}
            $db->table('application_documents')->where('id',$document['id'])->update(['storage_state'=>'deleted','storage_path'=>'deleted/'.$document['id'],'updated_at'=>Time::now()->toDateTimeString()]); $count++;
        }
        return $count;
    }

    private function claim(string $workerId): ?array
    {
        return service('transactional')->run(function ($db) use ($workerId): ?array {
            $table = $db->prefixTable('application_documents');
            $stale = date('Y-m-d H:i:s', time() - 900);
            $lock = $db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE SKIP LOCKED';
            $row = $db->query("SELECT * FROM {$table} WHERE storage_state='quarantined' AND scan_status IN ('pending','scanner_unavailable','scanning') AND (scan_locked_at IS NULL OR scan_locked_at < ?) ORDER BY created_at ASC LIMIT 1{$lock}", [$stale])->getRowArray();
            if ($row === null) return null;
            $db->table('application_documents')->where('id', $row['id'])->update(['scan_locked_at' => Time::now()->toDateTimeString(), 'scan_locked_by' => $workerId, 'scan_status' => 'scanning', 'scan_attempts' => ((int)$row['scan_attempts']) + 1]);
            return array_merge($row, ['scan_status' => 'scanning', 'scan_locked_by' => $workerId]);
        });
    }

    private function process(array $document, string $workerId): string
    {
        $tenant = (new TenantModel())->find((int) $document['tenant_id']);
        if ($tenant === null) throw new RuntimeException('Document tenant no longer exists.');
        service('tenantContextManager')->set(new TenantContext((int)$tenant['id'], (string)$tenant['slug'], 'worker'));
        try { $path = service('applicantDocumentStorage')->privateFile($document); $scan = $this->scanner->scan($path); }
        catch (Throwable $e) { $scan = ['status' => 'unavailable', 'detail' => $e->getMessage()]; }

        if ($scan['status'] === 'clean') {
            $promotion = service('applicantDocumentStorage')->promoteClean($document);
            try {
                service('transactional')->run(function () use ($document, $promotion, $workerId): array {
                    $model = new ApplicationDocumentModel();
                    $current = $model->find((int)$document['id']);
                    if ($current === null || $current['scan_locked_by'] !== $workerId || $current['scan_status'] !== 'scanning') throw new RuntimeException('Document scan claim was lost.');
                    if(! $model->update($document['id'], ['storage_path'=>$promotion['storage_path'],'storage_state'=>'active','scan_status'=>'clean','scan_error'=>null,'scanned_at'=>Time::now()->toDateTimeString(),'scan_locked_at'=>null,'scan_locked_by'=>null]))throw new RuntimeException('Clean document metadata could not be committed.');
                    service('auditLogger')->record('admissions.document.scan_clean', ['target_type'=>'application_document','target_id'=>$document['id'],'summary'=>'Applicant document passed malware scanning.']);
                    return [];
                });
            } catch (Throwable $e) { service('applicantDocumentStorage')->rollbackPromotion($promotion); throw $e; }
            return 'clean';
        }

        $status = $scan['status'] === 'infected' ? 'infected' : 'scanner_unavailable';
        service('transactional')->run(function () use ($document, $workerId, $status, $scan): array {
            $model = new ApplicationDocumentModel(); $current = $model->find((int)$document['id']);
            if ($current === null || $current['scan_locked_by'] !== $workerId) throw new RuntimeException('Document scan claim was lost.');
            $model->update($document['id'], ['storage_state'=>$status==='infected'?'rejected':'quarantined','scan_status'=>$status,'scan_error'=>mb_substr((string)($scan['detail']??''),0,500),'scanned_at'=>Time::now()->toDateTimeString(),'scan_locked_at'=>null,'scan_locked_by'=>null]);
            service('auditLogger')->record('admissions.document.scan_' . $status, ['target_type'=>'application_document','target_id'=>$document['id'],'summary'=>$status==='infected'?'Applicant document was rejected by malware scanning.':'Applicant document scan could not complete.']);
            return [];
        });
        return $status === 'infected' ? 'infected' : 'unavailable';
    }
}

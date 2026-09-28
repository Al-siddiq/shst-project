<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand; use CodeIgniter\CLI\CLI;
final class AdmissionsWorker extends BaseCommand
{
    protected $group='Operations'; protected $name='admissions:work'; protected $description='Deliver admissions notifications and scan quarantined documents.';
    public function run(array $params){$id=gethostname().'-'.getmypid();$limit=(int)($params[0]??50);$scan=service('documentScanner')->processBatch($id,$limit);$notify=service('notificationDelivery')->processBatch($id,$limit);CLI::write(json_encode(['scan'=>$scan,'notifications'=>$notify],JSON_PRETTY_PRINT));}
}

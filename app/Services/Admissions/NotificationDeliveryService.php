<?php

namespace App\Services\Admissions;

use App\Entities\TenantContext;
use App\Models\Tenant\Admissions\InAppNotificationModel;
use App\Models\TenantModel;
use App\Services\Notifications\EmailProviderInterface;
use CodeIgniter\I18n\Time;
use Config\Admissions;
use RuntimeException;
use Throwable;

/** Claims outbox rows and delivers email or durable in-app notifications. */
class NotificationDeliveryService
{
    public function __construct(private readonly EmailProviderInterface $email) {}

    public function processBatch(string $workerId, int $limit=50): array
    {
        $counts=['sent'=>0,'retry'=>0,'dead'=>0];
        for($i=0;$i<max(1,min(200,$limit));$i++){
            $record=$this->claim($workerId); if($record===null) break;
            $counts[$this->deliver($record,$workerId)]++;
        }
        service('tenantContextManager')->clear(); return $counts;
    }

    private function claim(string $workerId): ?array
    {
        return service('transactional')->run(function($db)use($workerId):?array{
            $table=$db->prefixTable('admission_notification_outbox'); $stale=date('Y-m-d H:i:s',time()-900);
            $lock=$db->DBDriver==='SQLite3'?'':' FOR UPDATE SKIP LOCKED';
            $row=$db->query("SELECT * FROM {$table} WHERE status IN ('pending','retry','processing') AND available_at<=? AND (locked_at IS NULL OR locked_at<?) ORDER BY available_at,id LIMIT 1{$lock}",[Time::now()->toDateTimeString(),$stale])->getRowArray();
            if($row===null)return null;
            $db->table('admission_notification_outbox')->where('id',$row['id'])->update(['status'=>'processing','locked_at'=>Time::now()->toDateTimeString(),'locked_by'=>$workerId]);
            return $row;
        });
    }

    private function deliver(array $record,string $workerId): string
    {
        $tenant=(new TenantModel())->find((int)$record['tenant_id']);
        if($tenant===null)return $this->fail($record,$workerId,new RuntimeException('Tenant no longer exists.'));
        service('tenantContextManager')->set(new TenantContext((int)$tenant['id'],(string)$tenant['slug'],'worker'));
        $payload=json_decode((string)$record['payload_json'],true)?:[];
        try{
            $message=$this->message((string)$record['event_name'],$payload);
            if($record['channel']==='email'){
                $providerId=$this->email->send((string)$record['recipient'],$message['title'],$message['body'],['event'=>$record['event_name'],'outbox_id'=>$record['id']]);
            }else{
                $userId=(int)($payload['user_id']??0); if($userId<1)throw new RuntimeException('In-app recipient could not be resolved.');
                $id=(new InAppNotificationModel())->insert(['user_id'=>$userId,'event_name'=>$record['event_name'],'title'=>$message['title'],'body'=>strip_tags($message['body']),'action_url'=>$message['url'],'created_at'=>Time::now()->toDateTimeString()],true);
                if(!$id)throw new RuntimeException('In-app notification could not be persisted.'); $providerId='in-app-'.$id;
            }
            service('transactional')->run(function($db)use($record,$workerId,$providerId):array{
                $db->table('admission_notification_outbox')->where('id',$record['id'])->where('locked_by',$workerId)->where('status','processing')->update(['status'=>'sent','sent_at'=>Time::now()->toDateTimeString(),'provider_message_id'=>$providerId,'locked_at'=>null,'locked_by'=>null,'last_error'=>null]);
                if($db->affectedRows()!==1)throw new RuntimeException('Notification claim was lost before completion.'); return [];
            });
            return 'sent';
        }catch(Throwable $e){return $this->fail($record,$workerId,$e);}
    }

    private function fail(array $record,string $workerId,Throwable $error): string
    {
        $config=config(Admissions::class); $attempts=((int)$record['attempts'])+1; $dead=$attempts >= $config->notificationMaxAttempts;
        $status=$dead?'dead':'retry'; $delay=$config->notificationRetryBaseSeconds*(2**max(0,$attempts-1));
        db_connect()->table('admission_notification_outbox')->where('id',$record['id'])->where('locked_by',$workerId)->update(['status'=>$status,'attempts'=>$attempts,'available_at'=>date('Y-m-d H:i:s',time()+$delay),'failed_at'=>$dead?Time::now()->toDateTimeString():null,'last_error'=>mb_substr($error->getMessage(),0,2000),'locked_at'=>null,'locked_by'=>null]);
        return $status;
    }

    private function message(string $event,array $payload): array
    {
        $application=(string)($payload['application_number']??'your application');
        return match($event){
            'admissions.application.submitted'=>['title'=>'Application submitted','body'=>'Your admission application '.$application.' was submitted successfully.','url'=>'/applicant'],
            'admissions.application.correction_requested'=>['title'=>'Application correction required','body'=>'Admissions requested corrections. '.(string)($payload['message']??''),'url'=>'/applicant'],
            'admissions.offer.issued'=>['title'=>'Admission offer issued','body'=>'An admission offer is available in your applicant portal.','url'=>'/applicant/offers'],
            'admissions.offer.accepted'=>['title'=>'Offer accepted','body'=>'Your admission offer acceptance was recorded.','url'=>'/applicant/offers'],
            'admissions.offer.declined'=>['title'=>'Offer declined','body'=>'Your admission offer decline was recorded.','url'=>'/applicant/offers'],
            'admissions.list.published'=>['title'=>'Admission list published','body'=>'An admission list containing your application has been published.','url'=>'/admissions/lists'],
            default=>['title'=>'Admissions update','body'=>'There is an update to your admission application.','url'=>'/applicant'],
        };
    }
}

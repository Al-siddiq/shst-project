<?php
namespace Tests\Database;

use App\Entities\TenantContext;
use App\Services\Admissions\NotificationDeliveryService;
use App\Services\Notifications\EmailProviderInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

final class Phase4NotificationDeliveryTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    protected $migrate=true; protected $namespace='App';

    public function testEmailOutboxIsClaimedAndMarkedSent(): void
    {
        $this->db->table('tenants')->insert(['school_name'=>'Notify School','slug'=>'notify-school','status'=>'active']); $tenant=(int)$this->db->insertID();
        service('tenantContextManager')->set(new TenantContext($tenant,'notify-school','test'));
        service('admissionNotificationDispatcher')->queue('admissions.application.submitted','applicant@example.test',['application_number'=>'APP-1'],'email','delivery-test');
        $provider=new class implements EmailProviderInterface{public array $sent=[];public function send(string $recipient,string $subject,string $html,array $metadata=[]):string{$this->sent[]=[$recipient,$subject];return 'provider-1';}};
        $result=(new NotificationDeliveryService($provider))->processBatch('test-worker',10);
        $row=$this->db->table('admission_notification_outbox')->where('tenant_id',$tenant)->get()->getRowArray();
        $this->assertSame(1,$result['sent']); $this->assertSame('sent',$row['status']); $this->assertSame('provider-1',$row['provider_message_id']); $this->assertCount(1,$provider->sent);
    }

    public function testInAppOutboxCreatesVisibleNotification(): void
    {
        $this->db->table('tenants')->insert(['school_name'=>'Inbox School','slug'=>'inbox-school','status'=>'active']); $tenant=(int)$this->db->insertID();
        service('tenantContextManager')->set(new TenantContext($tenant,'inbox-school','test'));
        service('admissionNotificationDispatcher')->queue('admissions.offer.issued',null,['user_id'=>9901,'offer_id'=>10],'in_app','in-app-test');
        $provider=new class implements EmailProviderInterface{public function send(string $recipient,string $subject,string $html,array $metadata=[]):string{throw new \RuntimeException('not expected');}};
        (new NotificationDeliveryService($provider))->processBatch('test-worker',10);
        $this->assertSame(1,$this->db->table('in_app_notifications')->where('tenant_id',$tenant)->where('user_id',9901)->countAllResults());
    }
}

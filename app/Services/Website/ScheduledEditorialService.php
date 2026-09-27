<?php
namespace App\Services\Website;

use App\Entities\TenantContext;
use App\Models\Tenant\Website\WebsiteContentItemModel;
use App\Models\TenantModel;
use CodeIgniter\I18n\Time;

/** Idempotently normalizes due content; read-time correctness remains authoritative. */
class ScheduledEditorialService
{
    public function normalizeAll(): int
    {
        $count=0;
        foreach((new TenantModel())->where('status','active')->findAll() as $tenant){
            service('tenantContextManager')->set(new TenantContext((int)$tenant['id'],(string)$tenant['slug'],'scheduler'));
            $items=(new WebsiteContentItemModel())->where('status','scheduled')->where('scheduled_for <=',Time::now()->toDateTimeString())->findAll(1000);
            foreach($items as $item){
                service('transactional')->run(function()use($item):array{
                    (new WebsiteContentItemModel())->update($item['id'],['status'=>'published','published_at'=>$item['scheduled_for'],'published_by'=>$item['published_by']]);
                    service('auditLogger')->record('website.content.schedule_normalized',['target_type'=>'website_content_item','target_id'=>$item['id'],'summary'=>'Scheduler normalized due public content.']); return [];
                });
                service('publicWebsiteCache')->bump('editorial.revision'); $count++;
            }
        }
        service('tenantContextManager')->clear(); return $count;
    }
}

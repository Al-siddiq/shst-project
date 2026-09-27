<?php
namespace App\Models\Tenant\Admissions;
use App\Models\TenantScopedModel;
final class InAppNotificationModel extends TenantScopedModel
{
    protected $table='in_app_notifications'; protected $primaryKey='id'; protected $returnType='array';
    protected $allowedFields=['tenant_id','user_id','event_name','title','body','action_url','read_at','created_at'];
    protected $beforeInsert=['applyTenantOnly'];
    protected function applyTenantOnly(array $data): array
    {
        $context=service('tenantContextManager')->current();
        if(! $context->isResolved()) throw new \RuntimeException('Tenant context is required for in-app notification.');
        $data['data']['tenant_id']=$context->tenantId;
        return $data;
    }
}

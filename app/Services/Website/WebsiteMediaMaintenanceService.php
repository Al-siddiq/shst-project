<?php
namespace App\Services\Website;
use App\Entities\TenantContext; use App\Models\TenantModel;
final class WebsiteMediaMaintenanceService
{
    public function reconcileAll():array{$total=['repaired'=>0,'missing_original'=>0];foreach((new TenantModel())->where('status','active')->findAll() as $tenant){service('tenantContextManager')->set(new TenantContext((int)$tenant['id'],(string)$tenant['slug'],'scheduler'));$result=service('websiteMedia')->reconcileDerivatives();foreach($total as $key=>$_)$total[$key]+=$result[$key];}service('tenantContextManager')->clear();return $total;}
}

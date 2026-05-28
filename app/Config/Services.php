<?php

namespace Config;

use App\Services\Tenancy\TenantContextManager;
use App\Services\Tenancy\TenantResolver;
use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    public static function tenantResolver(bool $getShared = true): TenantResolver
    {
        if ($getShared) {
            return static::getSharedInstance('tenantResolver');
        }

        return new TenantResolver();
    }

    public static function tenantContextManager(bool $getShared = true): TenantContextManager
    {
        if ($getShared) {
            return static::getSharedInstance('tenantContextManager');
        }

        return new TenantContextManager();
    }
}

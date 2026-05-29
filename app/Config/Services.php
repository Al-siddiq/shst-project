<?php

namespace Config;

use App\Services\NavigationResolver;
use App\Services\Tenancy\TenantContextManager;
use App\Services\Tenancy\TenantResolver;
use App\Services\TenantAccessService;
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

    public static function tenantAccess(bool $getShared = true): TenantAccessService
    {
        if ($getShared) {
            return static::getSharedInstance('tenantAccess');
        }

        return new TenantAccessService();
    }

    public static function navigationResolver(bool $getShared = true): NavigationResolver
    {
        if ($getShared) {
            return static::getSharedInstance('navigationResolver');
        }

        return new NavigationResolver();
    }
}

<?php

namespace Config;

use App\Services\AuditLogger;
use App\Services\LayoutResolver;
use App\Services\NavigationResolver;
use App\Services\ThemeResolver;
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

    public static function themeResolver(bool $getShared = true): ThemeResolver
    {
        if ($getShared) {
            return static::getSharedInstance('themeResolver');
        }

        return new ThemeResolver();
    }

    public static function layoutResolver(bool $getShared = true): LayoutResolver
    {
        if ($getShared) {
            return static::getSharedInstance('layoutResolver');
        }

        return new LayoutResolver();
    }

    public static function auditLogger(bool $getShared = true): AuditLogger
    {
        if ($getShared) {
            return static::getSharedInstance('auditLogger');
        }

        return new AuditLogger();
    }
}

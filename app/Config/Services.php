<?php

namespace Config;

use App\Services\AuditLogger;
use App\Services\LayoutResolver;
use App\Services\NavigationResolver;
use App\Services\ThemeResolver;
use App\Services\Tenancy\TenantContextManager;
use App\Services\Tenancy\TenantResolver;
use App\Services\TenantAccessService;
use App\Services\Website\MediaService;
use App\Services\Website\PublicTenantGuard;
use App\Services\Website\WebsiteAuthorityProvisioner;
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

    /**
     * Shared guard keeps anonymous tenant eligibility consistent for public
     * pages and media routes without coupling that decision to controllers.
     */
    public static function publicTenantGuard(bool $getShared = true): PublicTenantGuard
    {
        if ($getShared) {
            return static::getSharedInstance('publicTenantGuard');
        }

        return new PublicTenantGuard();
    }

    /**
     * Shared media service centralizes safe storage, derivatives, and audits.
     */
    public static function websiteMedia(bool $getShared = true): MediaService
    {
        if ($getShared) {
            return static::getSharedInstance('websiteMedia');
        }

        return new MediaService();
    }

    /**
     * Provisioning remains a service so onboarding and access-management flows
     * cannot diverge when granting tenant-super-admin website capabilities.
     */
    public static function websiteAuthorityProvisioner(bool $getShared = true): WebsiteAuthorityProvisioner
    {
        if ($getShared) {
            return static::getSharedInstance('websiteAuthorityProvisioner');
        }

        return new WebsiteAuthorityProvisioner();
    }
}

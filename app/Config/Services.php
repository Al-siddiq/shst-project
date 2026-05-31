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
use App\Services\Website\PublicShowcaseService;
use App\Services\Website\PublicWebsiteCache;
use App\Services\Website\PublicWebsiteService;
use App\Services\Website\PublicWebsiteUrlGenerator;
use App\Services\Website\WebsiteAuthorityProvisioner;
use App\Services\Website\WebsiteMenuService;
use App\Services\Website\WebsiteSettingsService;
use App\Services\Website\ShowcaseManagementService;
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

    /** Tenant-aware public URL rules belong outside views and controllers. */
    public static function publicWebsiteUrl(bool $getShared = true): PublicWebsiteUrlGenerator
    {
        if ($getShared) {
            return static::getSharedInstance('publicWebsiteUrl');
        }

        return new PublicWebsiteUrlGenerator();
    }

    /** Tenant namespaces are mandatory for every public-site cache entry. */
    public static function publicWebsiteCache(bool $getShared = true): PublicWebsiteCache
    {
        if ($getShared) {
            return static::getSharedInstance('publicWebsiteCache');
        }

        return new PublicWebsiteCache();
    }

    /** Resolves tenant profile fallbacks and CMS website settings. */
    public static function websiteSettings(bool $getShared = true): WebsiteSettingsService
    {
        if ($getShared) {
            return static::getSharedInstance('websiteSettings');
        }

        return new WebsiteSettingsService();
    }

    /** Resolves visible, ordered, tenant-owned public menu records. */
    public static function websiteMenu(bool $getShared = true): WebsiteMenuService
    {
        if ($getShared) {
            return static::getSharedInstance('websiteMenu');
        }

        return new WebsiteMenuService();
    }

    /** Builds shared view data for the lightweight server-rendered public site. */
    public static function publicWebsite(bool $getShared = true): PublicWebsiteService
    {
        if ($getShared) {
            return static::getSharedInstance('publicWebsite');
        }

        return new PublicWebsiteService();
    }

    /** Resolves published academic showcase records for anonymous visitors. */
    public static function publicShowcase(bool $getShared = true): PublicShowcaseService
    {
        if ($getShared) {
            return static::getSharedInstance('publicShowcase');
        }

        return new PublicShowcaseService();
    }

    /** Owns tenant-admin showcase writes, publication checks, audits, and cache invalidation. */
    public static function showcaseManagement(bool $getShared = true): ShowcaseManagementService
    {
        if ($getShared) {
            return static::getSharedInstance('showcaseManagement');
        }

        return new ShowcaseManagementService();
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

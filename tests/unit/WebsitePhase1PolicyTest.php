<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Protects Phase 1 structural decisions while database integration tests remain
 * available to environments with installed Composer dependencies.
 *
 * @internal
 */
final class WebsitePhase1PolicyTest extends CIUnitTestCase
{
    public function testPublicCoreMigrationDefinesTenantOwnedSettingsAndMenuTables(): void
    {
        $migration = file_get_contents(APPPATH . 'Database/Migrations/2026-05-31-120000_CreatePublicWebsiteCoreTables.php');

        $this->assertStringContainsString("createTable('website_settings'", $migration);
        $this->assertStringContainsString("'is_public_enabled'", $migration);
        $this->assertStringContainsString("createTable('website_menu_items'", $migration);
        $this->assertStringContainsString("'tenant_id'", $migration);
    }

    public function testTenantAwareCacheKeyContainsTenantNamespace(): void
    {
        $cacheService = file_get_contents(APPPATH . 'Services/Website/PublicWebsiteCache.php');

        $this->assertStringContainsString("'public-site.tenant-' . $context->tenantId", $cacheService);
    }

    public function testPublicShellProvidesMobileNavigationAndTenantThemeVariables(): void
    {
        $layout = file_get_contents(APPPATH . 'Views/layouts/public.php');

        $this->assertStringContainsString('--tenant-primary', $layout);
        $this->assertStringContainsString('data-menu-toggle', $layout);
        $this->assertStringContainsString('Skip to content', $layout);
    }
}

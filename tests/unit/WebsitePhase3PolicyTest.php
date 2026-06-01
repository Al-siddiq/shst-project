<?php

use CodeIgniter\Test\CIUnitTestCase;

/** Protects the Phase 3 lifecycle and public-query contract. @internal */
final class WebsitePhase3PolicyTest extends CIUnitTestCase
{
    public function testMigrationDefinesEditorialContentAndLifecycleFields(): void
    {
        $migration = file_get_contents(APPPATH . 'Database/Migrations/2026-05-31-160000_CreateWebsiteEditorialContentTable.php');

        $this->assertStringContainsString("createTable('website_content_items'", $migration);
        $this->assertStringContainsString("'scheduled_for'", $migration);
        $this->assertStringContainsString("'published_at'", $migration);
        $this->assertStringContainsString("'archived_at'", $migration);
    }

    public function testPublicEditorialQueryAllowsPublishedAndDueScheduledRecordsOnly(): void
    {
        $service = file_get_contents(APPPATH . 'Services/Website/PublicEditorialService.php');

        $this->assertStringContainsString("where('status', 'published')", $service);
        $this->assertStringContainsString("where('status', 'scheduled')", $service);
        $this->assertStringContainsString("where('scheduled_for <=', $now)", $service);
        $this->assertStringContainsString("where('audience', 'public')", $service);
        $this->assertStringContainsString("orWhere('event_start_at <=', $now)", $service);
        $this->assertStringContainsString("where('visibility', 'public')", $service);
        $this->assertStringContainsString('publicCacheTtl', $service);
    }

    public function testManagementServiceUsesExplicitLifecycleCommandsAndRevisionInvalidation(): void
    {
        $service = file_get_contents(APPPATH . 'Services/Website/EditorialManagementService.php');

        foreach (['saveDraft', 'schedule', 'publish', 'archive', 'delete'] as $command) {
            $this->assertStringContainsString('function ' . $command, $service);
        }
        $this->assertStringContainsString("bump('editorial.revision')", $service);
    }
}

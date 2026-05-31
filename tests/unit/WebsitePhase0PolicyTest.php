<?php

use App\Services\Website\WebsiteAuthorityCatalog;
use CodeIgniter\Test\CIUnitTestCase;
use Config\WebsiteMedia;

/**
 * Guards the Phase 0 product policy from accidental drift while later website
 * phases add CMS screens and content models on top of this readiness layer.
 *
 * @internal
 */
final class WebsitePhase0PolicyTest extends CIUnitTestCase
{
    public function testWebsiteAuthorityCatalogContainsRequiredCapabilities(): void
    {
        $this->assertSame([
            'website.dashboard.view',
            'website.settings.manage',
            'website.content.view',
            'website.content.create',
            'website.content.edit',
            'website.content.publish',
            'website.content.archive',
            'website.content.delete',
            'website.menu.manage',
            'website.media.manage',
            'website.audit.view',
        ], array_keys(WebsiteAuthorityCatalog::AUTHORITIES));
    }

    public function testWebsiteMediaPolicySeparatesPublicAndPrivateVisibility(): void
    {
        $config = config(WebsiteMedia::class);

        $this->assertSame(['public', 'private'], $config->allowedVisibilities);
        $this->assertContains('gallery_image', $config->allowedCategories);
        $this->assertArrayHasKey('thumbnail', $config->derivatives);
        $this->assertArrayHasKey('medium', $config->derivatives);
        $this->assertArrayHasKey('large', $config->derivatives);
    }

    public function testWebsiteMediaPolicyMapsOnlySupportedImageTypes(): void
    {
        $config = config(WebsiteMedia::class);

        $this->assertSame([
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ], $config->extensionsByMimeType);
    }
}

<?php

namespace App\Services\Website;

/**
 * Stable Block 2 capability catalogue.
 *
 * Authorities are product-level codes but are provisioned into each tenant's
 * operational-authority records. Keeping their definitions together prevents
 * routes, seeders, and later onboarding code from drifting apart.
 */
class WebsiteAuthorityCatalog
{
    /** @var array<string, string> */
    public const AUTHORITIES = [
        'website.dashboard.view' => 'View website dashboard',
        'website.settings.manage' => 'Manage website settings',
        'website.content.view' => 'View website content',
        'website.content.create' => 'Create website content',
        'website.content.edit' => 'Edit website content',
        'website.content.publish' => 'Publish website content',
        'website.content.archive' => 'Archive website content',
        'website.content.delete' => 'Delete website content',
        'website.menu.manage' => 'Manage website menu',
        'website.media.manage' => 'Manage website media',
        'website.audit.view' => 'View website audit trail',
    ];
}

<?php

namespace App\Models\Tenant\Website;

use App\Models\TenantScopedModel;

/**
 * Tenant-owned public navigation item.
 *
 * Navigation is stored instead of hard-coded so each school decides which
 * public pages are promoted and how visitors move through its website.
 */
class WebsiteMenuItemModel extends TenantScopedModel
{
    protected $table = 'website_menu_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tenant_id', 'parent_id', 'label', 'link_type', 'route_name', 'url', 'content_slug',
        'target', 'sort_order', 'status', 'is_visible', 'created_by', 'updated_by',
    ];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
}

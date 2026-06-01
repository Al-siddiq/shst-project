<?php

namespace App\Models\Tenant\Website;

use App\Models\TenantScopedModel;

/** Tenant-scoped editorial content with an auditable public lifecycle. */
class WebsiteContentItemModel extends TenantScopedModel
{
    protected $table = 'website_content_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tenant_id', 'content_type', 'title', 'slug', 'summary', 'body', 'featured_media_id',
        'category', 'related_department_id', 'related_programme_id', 'author_display_name',
        'announcement_type', 'priority', 'audience', 'event_start_at', 'event_end_at',
        'academic_session_id', 'semester_id', 'visibility', 'status', 'scheduled_for',
        'published_at', 'archived_at', 'published_by', 'seo_title', 'seo_description',
        'created_by', 'updated_by',
    ];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
}

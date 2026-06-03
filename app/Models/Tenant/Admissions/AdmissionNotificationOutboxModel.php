<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Tenant-scoped notification intent; actual delivery is intentionally deferred. */
class AdmissionNotificationOutboxModel extends TenantScopedModel
{
    protected $table = 'admission_notification_outbox';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'event_name', 'channel', 'recipient', 'payload_json', 'status', 'available_at', 'attempts', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}

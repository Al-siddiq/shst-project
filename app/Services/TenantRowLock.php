<?php

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

/** Explicitly locks an allow-listed tenant row during a service-owned transaction. */
class TenantRowLock
{
    private const TABLES = [
        'applicant_profiles', 'applicant_applications', 'application_documents', 'admission_decisions',
        'admission_offers', 'admission_list_publications', 'admission_shortlist_batches',
    ];

    public function lock(string $table, int $id): array
    {
        $context = service('tenantContextManager')->current();
        if (! $context->isResolved() || ! in_array($table, self::TABLES, true) || $id < 1) {
            throw new InvalidArgumentException('A valid tenant row lock target is required.');
        }
        $db = db_connect();
        $suffix = $db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE';
        $row = $db->query('SELECT * FROM ' . $db->prefixTable($table) . ' WHERE id=? AND tenant_id=?' . $suffix, [$id, $context->tenantId])->getRowArray();
        if ($row === null) {
            throw new RuntimeException('The tenant resource changed or is unavailable.');
        }

        return $row;
    }
}

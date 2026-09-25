<?php

namespace App\Services\Admissions;

use Config\Admissions;
use RuntimeException;

/**
 * Tenant-aware admission reference generator skeleton.
 *
 * Phase 0 establishes tenant-separated transactional counters. Phase 3 calls
 * this inside final submission and adds driver-specific concurrency tests before
 * application references become externally visible.
 */
class AdmissionReferenceGenerator
{
    public function next(string $namespace = 'application'): string
    {
        $context = service('tenantContextManager')->current();
        if (! $context->isResolved()) {
            throw new RuntimeException('Tenant context is required before allocating an admission reference.');
        }

        $db = db_connect();
        $value = service('transactional')->run(function ($db) use ($context, $namespace): int {
            $table = $db->prefixTable('admission_reference_sequences');
            if ($db->DBDriver === 'SQLite3') {
                $db->query("INSERT OR IGNORE INTO {$table} (tenant_id, namespace, next_value) VALUES (?, ?, 1)", [$context->tenantId, $namespace]);
                $suffix = '';
            } else {
                $db->query("INSERT IGNORE INTO {$table} (tenant_id, namespace, next_value) VALUES (?, ?, 1)", [$context->tenantId, $namespace]);
                $suffix = ' FOR UPDATE';
            }
            $sequence = $db->query("SELECT id, next_value FROM {$table} WHERE tenant_id = ? AND namespace = ?{$suffix}", [$context->tenantId, $namespace])->getRowArray();
            if ($sequence === null) {
                throw new RuntimeException('Unable to initialize admission reference sequence.');
            }
            $value = (int) $sequence['next_value'];
            $db->table('admission_reference_sequences')->where('id', $sequence['id'])->where('next_value', $value)->update(['next_value' => $value + 1]);
            if ($db->affectedRows() !== 1) {
                throw new RuntimeException('Concurrent admission reference allocation was not serialized.');
            }

            return $value;
        });

        $config = config(Admissions::class);

        return sprintf('%s-%s-%0' . $config->applicationReferencePadding . 'd', $config->applicationReferencePrefix, $context->tenantId, $value);
    }
}

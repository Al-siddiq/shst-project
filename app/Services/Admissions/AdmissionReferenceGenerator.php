<?php

namespace App\Services\Admissions;

use App\Models\Tenant\Admissions\AdmissionReferenceSequenceModel;
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
        $db->transStart();
        $builder = $db->table('admission_reference_sequences');
        $sequence = $builder
            ->where('tenant_id', $context->tenantId)
            ->where('namespace', $namespace)
            ->get()
            ->getRowArray();

        if ($sequence === null) {
            (new AdmissionReferenceSequenceModel())->insert(['namespace' => $namespace, 'next_value' => 2]);
            $value = 1;
        } else {
            $value = (int) $sequence['next_value'];
            (new AdmissionReferenceSequenceModel())->update((int) $sequence['id'], ['next_value' => $value + 1]);
        }
        $db->transComplete();

        if (! $db->transStatus()) {
            throw new RuntimeException('Unable to allocate admission reference.');
        }

        $config = config(Admissions::class);

        return sprintf('%s-%s-%0' . $config->applicationReferencePadding . 'd', $config->applicationReferencePrefix, $context->tenantId, $value);
    }
}

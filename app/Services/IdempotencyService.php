<?php

namespace App\Services;

use CodeIgniter\I18n\Time;
use InvalidArgumentException;
use RuntimeException;

/** Replays a tenant command result and rejects reuse with different input. */
class IdempotencyService
{
    public function execute(string $operation, string $key, array $request, callable $command): array
    {
        $context = service('tenantContextManager')->current();
        $operation = trim($operation);
        $key = trim($key);
        if (! $context->isResolved() || $operation === '' || $key === '' || strlen($key) > 100) {
            throw new InvalidArgumentException('Tenant, operation, and a valid idempotency key are required.');
        }
        $hash = hash('sha256', json_encode($this->canonicalize($request), JSON_UNESCAPED_SLASHES));

        return service('transactional')->run(function ($db) use ($context, $operation, $key, $hash, $command): array {
            $table = $db->prefixTable('idempotency_records');
            $insert = $db->DBDriver === 'SQLite3' ? 'INSERT OR IGNORE' : 'INSERT IGNORE';
            $db->query("{$insert} INTO {$table} (tenant_id, operation, idempotency_key, request_hash, status, created_at) VALUES (?, ?, ?, ?, 'processing', ?)", [$context->tenantId, $operation, $key, $hash, Time::now()->toDateTimeString()]);
            $lock = $db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE';
            $record = $db->query("SELECT * FROM {$table} WHERE tenant_id=? AND operation=? AND idempotency_key=?{$lock}", [$context->tenantId, $operation, $key])->getRowArray();
            if ($record === null) throw new RuntimeException('Idempotency record could not be locked.');
            if (! hash_equals((string) $record['request_hash'], $hash)) {
                throw new InvalidArgumentException('Idempotency key was already used with different input.');
            }
            if ($record['status'] === 'completed') {
                return json_decode((string) $record['response_json'], true, 512, JSON_THROW_ON_ERROR);
            }

            $response = $command();
            if (! is_array($response)) throw new RuntimeException('Idempotent commands must return an array response.');
            $db->table('idempotency_records')->where('id', $record['id'])->update([
                'status' => 'completed', 'response_json' => json_encode($response, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            ]);
            return $response;
        });
    }

    private function canonicalize(array $value): array
    {
        ksort($value);
        foreach ($value as &$item) if (is_array($item)) $item = $this->canonicalize($item);
        return $value;
    }
}

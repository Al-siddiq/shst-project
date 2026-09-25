<?php

namespace App\Services\Website;

use Closure;
use RuntimeException;

/**
 * Adds a mandatory tenant namespace to public website cache keys.
 *
 * Shared caching without a tenant key is a cross-tenant leakage risk. Services
 * use this wrapper instead of calling CI4 cache handlers directly so that risk
 * cannot be reintroduced accidentally as the public site grows.
 */
class PublicWebsiteCache
{
    public function revision(string $key): int
    {
        $context = service('tenantContextManager')->current();
        if (! $context->isResolved()) {
            throw new RuntimeException('Tenant context is required before reading a cache revision.');
        }
        $row = db_connect()->table('tenant_cache_revisions')->select('revision')
            ->where('tenant_id', $context->tenantId)->where('namespace', $key)->get()->getRowArray();

        return (int) ($row['revision'] ?? 1);
    }

    public function remember(string $key, Closure $loader, int $ttl = 300): mixed
    {
        $cache = cache();
        $tenantKey = $this->key($key);
        $value = $cache->get($tenantKey);

        if ($value !== null) {
            return $value;
        }

        $value = $loader();
        $cache->save($tenantKey, $value, $ttl);

        return $value;
    }

    public function forget(string $key): void
    {
        cache()->delete($this->key($key));
    }

    /** @param list<string> $keys */
    public function forgetMany(array $keys): void
    {
        // Each deletion still passes through key(), preserving the mandatory
        // tenant namespace even when one CMS write affects several fragments.
        foreach ($keys as $key) {
            $this->forget($key);
        }
    }

    /**
     * Advances a tenant-scoped cache generation. Versioned keys make every
     * paginated editorial cache entry unreachable after one lifecycle change.
     */
    public function bump(string $key): int
    {
        $cache = cache();
        $tenantKey = $this->key($key);
        $context = service('tenantContextManager')->current();
        $value = service('transactional')->run(function ($db) use ($context, $key): int {
            $table = $db->prefixTable('tenant_cache_revisions');
            $insert = $db->DBDriver === 'SQLite3' ? 'INSERT OR IGNORE' : 'INSERT IGNORE';
            $db->query("{$insert} INTO {$table} (tenant_id, namespace, revision, updated_at) VALUES (?, ?, 1, ?)", [$context->tenantId, $key, date('Y-m-d H:i:s')]);
            $db->query("UPDATE {$table} SET revision = revision + 1, updated_at = ? WHERE tenant_id = ? AND namespace = ?", [date('Y-m-d H:i:s'), $context->tenantId, $key]);
            $row = $db->query("SELECT revision FROM {$table} WHERE tenant_id = ? AND namespace = ?", [$context->tenantId, $key])->getRowArray();
            if ($row === null) throw new RuntimeException('Tenant cache revision could not be advanced atomically.');
            return (int) $row['revision'];
        });
        $cache->save($tenantKey, $value, 86400);

        return $value;
    }

    public function key(string $key): string
    {
        $context = service('tenantContextManager')->current();
        if (! $context->isResolved()) {
            throw new RuntimeException('Tenant context is required before using the public website cache.');
        }

        // Reserved cache characters are removed so configuration remains
        // portable across CI4 file, Redis, and Memcached handlers.
        $safeKey = preg_replace('/[^a-zA-Z0-9_.-]+/', '-', $key) ?: 'default';

        return 'public-site.tenant-' . $context->tenantId . '.' . trim($safeKey, '-');
    }
}

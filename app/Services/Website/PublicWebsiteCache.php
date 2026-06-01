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
        $value = (int) ($cache->get($tenantKey) ?? 0) + 1;
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

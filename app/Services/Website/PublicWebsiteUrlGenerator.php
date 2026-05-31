<?php

namespace App\Services\Website;

use App\Entities\TenantContext;
use App\Models\TenantDomainModel;
use App\Models\TenantModel;
use InvalidArgumentException;

/**
 * Generates public links without dropping the current tenant context.
 *
 * Custom domains and subdomains can use normal root-relative URLs. Route-slug
 * resolution needs a `/t/{slug}` prefix, especially in local development and
 * controlled preview deployments. Centralizing this rule prevents links from
 * silently switching schools as new public pages are added.
 */
class PublicWebsiteUrlGenerator
{
    /** @var array<string, string> */
    private const ROUTES = [
        'home' => '',
        'about' => 'about',
        'contact' => 'contact',
    ];

    public function route(string $name, array $parameters = []): string
    {
        $path = self::ROUTES[$name] ?? null;
        if ($path === null) {
            throw new InvalidArgumentException('Unsupported public website route.');
        }

        foreach ($parameters as $parameter) {
            $path .= '/' . rawurlencode((string) $parameter);
        }

        return $this->path($path);
    }

    public function path(string $path = ''): string
    {
        $context = service('tenantContextManager')->current();
        if (! $context->isResolved()) {
            throw new InvalidArgumentException('Tenant context is required before generating public links.');
        }

        $prefix = $context->source === 'route_slug' ? 't/' . $this->tenantSlug($context) : '';
        $segments = array_filter([trim($prefix, '/'), trim($path, '/')], static fn (string $segment): bool => $segment !== '');

        return site_url(implode('/', $segments));
    }

    /**
     * Provides a canonical origin for SEO. An active primary domain wins; the
     * configured base URL and tenant-aware path remain a safe development fallback.
     */
    public function canonical(string $name, array $parameters = []): string
    {
        $context = service('tenantContextManager')->current();
        if (! $context->isResolved()) {
            throw new InvalidArgumentException('Tenant context is required before generating canonical links.');
        }

        $domain = (new TenantDomainModel())
            ->where('tenant_id', $context->tenantId)
            ->where('is_primary', 1)
            ->where('status', 'active')
            ->first();

        if ($domain === null) {
            return $this->route($name, $parameters);
        }

        $path = self::ROUTES[$name] ?? null;
        if ($path === null) {
            throw new InvalidArgumentException('Unsupported public website route.');
        }
        foreach ($parameters as $parameter) {
            $path .= '/' . rawurlencode((string) $parameter);
        }

        return 'https://' . $domain['domain'] . ($path === '' ? '/' : '/' . ltrim($path, '/'));
    }

    private function tenantSlug(TenantContext $context): string
    {
        if ($context->slug !== null) {
            return $context->slug;
        }

        $tenant = (new TenantModel())->find($context->tenantId);
        if ($tenant === null) {
            throw new InvalidArgumentException('Resolved tenant could not be loaded.');
        }

        return $tenant['slug'];
    }
}

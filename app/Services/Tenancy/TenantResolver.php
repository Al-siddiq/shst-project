<?php

namespace App\Services\Tenancy;

use App\Entities\TenantContext;
use App\Models\TenantDomainModel;
use App\Models\TenantMembershipModel;
use App\Models\TenantModel;
use CodeIgniter\HTTP\IncomingRequest;

class TenantResolver
{
    public function resolve(IncomingRequest $request): TenantContext
    {
        // Priority 1: Custom domain mapping
        $host = strtolower((string) $request->getServer('HTTP_HOST'));
        $host = explode(':', $host)[0];

        $domain = (new TenantDomainModel())
            ->where('domain', $host)
            ->where('status', 'active')
            ->first();

        if ($domain !== null) {
            return new TenantContext((int) $domain['tenant_id'], null, 'custom_domain');
        }

        // Priority 2: Subdomain mapping through tenant slug.
        $subdomain = $this->resolveSubdomain($host);
        if ($subdomain !== null) {
            $tenant = (new TenantModel())->where('slug', $subdomain)->first();
            if ($tenant !== null) {
                return new TenantContext((int) $tenant['id'], (string) $tenant['slug'], 'subdomain');
            }
        }

        // Priority 3: Explicit slug route segment (/t/{slug}/...)
        $slug = $request->getUri()->getSegment(2);
        if ($request->getUri()->getSegment(1) === 't' && $slug !== '') {
            $tenant = (new TenantModel())->where('slug', $slug)->first();
            if ($tenant !== null) {
                return new TenantContext((int) $tenant['id'], (string) $tenant['slug'], 'route_slug');
            }
        }

        // Priority 4: Authenticated user's default active membership
        $userId = (int) session('user_id');
        if ($userId > 0) {
            $membership = (new TenantMembershipModel())
                ->where('user_id', $userId)
                ->where('is_active', 1)
                ->orderBy('is_default', 'DESC')
                ->first();

            if ($membership !== null) {
                return new TenantContext((int) $membership['tenant_id'], null, 'membership');
            }
        }

        // Priority 5: unresolved
        return new TenantContext(null, null, null);
    }

    private function resolveSubdomain(string $host): ?string
    {
        $parts = explode('.', $host);

        if (count($parts) < 3) {
            return null;
        }

        return $parts[0] !== '' ? $parts[0] : null;
    }
}

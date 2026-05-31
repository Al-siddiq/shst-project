<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Stops anonymous delivery when a tenant is unresolved or not active.
 *
 * The neutral response intentionally avoids school identity and record details,
 * preventing suspended or invalid tenants from becoming an information oracle.
 */
class PublicTenantFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $context = service('tenantContextManager')->current();
        if (service('publicTenantGuard')->allows($context)) {
            return null;
        }

        return service('response')
            ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
            ->setBody(view('public_site/unavailable'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}

<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class TenantContextFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $resolver = service('tenantResolver');
        $context = $resolver->resolve($request);

        service('tenantContextManager')->set($context);

        if (in_array('required', $arguments ?? [], true) && ! $context->isResolved()) {
            return service('response')
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Tenant context is required for this endpoint.',
                    'data' => [],
                    'errors' => ['tenant' => 'Tenant could not be resolved from request context.'],
                ]);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}

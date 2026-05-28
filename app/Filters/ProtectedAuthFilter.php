<?php

namespace App\Filters;

use App\Libraries\Auth\IdentityGuard;
use App\Traits\ApiResponseTrait;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ProtectedAuthFilter implements FilterInterface
{
    use ApiResponseTrait;

    public function before(RequestInterface $request, $arguments = null)
    {
        $guard = new IdentityGuard();
        if ($guard->check()) {
            return null;
        }

        $wantsJson = $request->isAJAX() || str_contains((string) $request->getHeaderLine('Accept'), 'application/json');
        if ($wantsJson) {
            return service('response')
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Authentication required.',
                    'data' => [],
                    'errors' => ['auth' => 'You must be signed in to access this resource.'],
                ]);
        }

        return redirect()->to('/login')->with('error', 'Authentication required.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}

<?php

namespace App\Filters;

use App\Libraries\Auth\IdentityGuard;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Keeps platform identities outside ordinary tenant membership. */
class PlatformAccessFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $guard = new IdentityGuard();
        if ($guard->isPlatformAdministrator()
            && service('tenantIdentity')->membershipForUser($guard->userId()) === null) {
            return null;
        }

        return service('response')->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
            'status' => 'error',
            'message' => 'Platform administrator access is required.',
            'data' => [],
            'errors' => ['authorization' => 'This account cannot access the platform administration area.'],
        ]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}

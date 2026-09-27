<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Conservative per-IP throttle for authentication and privileged mutations. */
class SensitiveRateLimitFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (in_array(strtolower($request->getMethod()), ['get', 'head', 'options'], true)) {
            return null;
        }

        $capacity = max(1, (int) ($arguments[0] ?? 10));
        $seconds = max(10, (int) ($arguments[1] ?? 60));
        $key = 'sensitive:' . hash('sha256', $request->getIPAddress() . '|' . $request->getUri()->getPath());
        if (service('throttler')->check($key, $capacity, $seconds)) {
            return null;
        }

        return service('response')
            ->setStatusCode(ResponseInterface::HTTP_TOO_MANY_REQUESTS)
            ->setHeader('Retry-After', (string) $seconds)
            ->setJSON([
                'status' => 'error',
                'message' => 'Too many attempts. Please wait before retrying.',
                'data' => [],
                'errors' => ['rate_limit' => 'The security attempt limit has been reached.'],
            ]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}

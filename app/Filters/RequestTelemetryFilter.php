<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Adds a safe correlation ID and one structured request-completion event. */
final class RequestTelemetryFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $incoming = trim($request->getHeaderLine('X-Request-ID'));
        $requestId = preg_match('/^[A-Za-z0-9-]{8,64}$/', $incoming) === 1
            ? $incoming
            : bin2hex(random_bytes(16));
        $request->setHeader('X-Request-ID', $requestId);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $requestId = $request->getHeaderLine('X-Request-ID');
        $response->setHeader('X-Request-ID', $requestId);
        $segments = array_map(static function (string $segment): string {
            return ctype_digit($segment) || strlen($segment) > 16 ? '{redacted}' : $segment;
        }, $request->getUri()->getSegments());
        log_message('info', json_encode([
            'event' => 'http.request.completed',
            'request_id' => $requestId,
            'method' => $request->getMethod(),
            'path' => '/' . implode('/', $segments),
            'status' => $response->getStatusCode(),
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

        return null;
    }
}

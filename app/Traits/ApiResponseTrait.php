<?php

namespace App\Traits;

use CodeIgniter\HTTP\ResponseInterface;

trait ApiResponseTrait
{
    protected function ok(string $message, array $data = [], int $status = ResponseInterface::HTTP_OK)
    {
        return $this->response->setStatusCode($status)->setJSON([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'errors' => [],
        ]);
    }

    protected function fail(string $message, array $errors = [], int $status = ResponseInterface::HTTP_BAD_REQUEST)
    {
        return $this->response->setStatusCode($status)->setJSON([
            'status' => 'error',
            'message' => $message,
            'data' => [],
            'errors' => $errors,
        ]);
    }
}

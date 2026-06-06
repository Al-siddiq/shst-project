<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Rejects applicant portal requests without an owned active tenant profile. */
class ApplicantAccessFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $context = service('tenantContextManager')->current();
        if (service('applicantAccessPolicy')->currentProfile($context) !== null) {
            return null;
        }

        return service('response')
            ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
            ->setJSON([
                'status' => 'error',
                'message' => 'Applicant access is not available for this tenant account.',
                'data' => [],
                'errors' => ['applicant' => 'An active applicant profile owned by the authenticated user is required.'],
            ]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}

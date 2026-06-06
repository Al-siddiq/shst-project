<?php

namespace App\Controllers\Tenant\Admissions;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;
use InvalidArgumentException;

/** Tenant staff workspace for Phase 4 application review and screening. */
class ApplicationReviewController extends BaseController
{
    use ApiResponseTrait;

    protected $helpers = ['form'];

    public function index(): string
    {
        return view('tenant/admissions/applications/index', service('admissionReview')->queue($this->request->getGet()));
    }

    public function show(int $id): string
    {
        return view('tenant/admissions/applications/show', service('admissionReview')->detail($id));
    }

    public function review(int $id)
    {
        return $this->persist(static fn (array $payload): int => service('admissionReview')->reviewApplication($id, $payload), 'Application review saved.');
    }

    public function reviewDocument(int $id)
    {
        return $this->persist(static fn (array $payload): int => service('admissionReview')->reviewDocument($id, $payload), 'Document review saved.');
    }

    public function screening(int $id)
    {
        return $this->persist(static fn (array $payload): int => service('admissionReview')->recordScreening($id, $payload), 'Screening record saved.');
    }

    public function audit(): string
    {
        return view('tenant/admissions/applications/audit', ['auditEvents' => service('admissionReview')->auditEvents()]);
    }

    /** @param callable(array<string,mixed>):int $callback */
    private function persist(callable $callback, string $message)
    {
        try {
            $id = $callback($this->request->getPost());
        } catch (InvalidArgumentException $exception) {
            return $this->request->isAJAX()
                ? $this->fail('Validation failed.', ['admissions' => $exception->getMessage()], 422)
                : redirect()->back()->withInput()->with('errors', ['admissions' => $exception->getMessage()]);
        }

        return $this->request->isAJAX()
            ? $this->ok($message, ['id' => $id])
            : redirect()->back()->with('message', $message);
    }
}

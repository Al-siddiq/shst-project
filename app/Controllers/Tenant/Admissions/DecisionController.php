<?php

namespace App\Controllers\Tenant\Admissions;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;
use InvalidArgumentException;

/** Tenant staff Phase 5 shortlisting, decision, approval, and offer workspace. */
class DecisionController extends BaseController
{
    use ApiResponseTrait;

    protected $helpers = ['form'];

    public function index(): string
    {
        return view('tenant/admissions/decisions/index', service('admissionDecision')->workspace());
    }

    public function createBatch()
    {
        return $this->persist(static fn (array $payload): int => service('admissionDecision')->createBatch($payload), 'Decision batch created.');
    }

    public function addToBatch(int $batchId, int $applicationId)
    {
        return $this->persist(static fn (array $payload): int => service('admissionDecision')->addToBatch($batchId, $applicationId, $payload), 'Application added to decision batch.');
    }

    public function decide(int $applicationId)
    {
        return $this->persist(static fn (array $payload): int => service('admissionDecision')->decide($applicationId, $payload), 'Admission decision saved.');
    }

    public function approve(int $decisionId)
    {
        return $this->persist(static fn (array $payload): int => service('admissionDecision')->approveDecision($decisionId, $payload), 'Admission offer decision approved.');
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

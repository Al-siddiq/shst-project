<?php

namespace App\Controllers\Tenant\Admissions;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;
use InvalidArgumentException;

/** Tenant staff Phase 7 acceptance, clearance, and handoff tracker. */
class AcceptanceController extends BaseController
{
    use ApiResponseTrait;

    protected $helpers = ['form'];

    public function index(): string
    {
        return view('tenant/admissions/acceptance/index', service('admissionAcceptance')->staffWorkspace());
    }

    public function clearance(int $applicationId)
    {
        return $this->persist(static fn (array $payload): int => service('admissionAcceptance')->updateClearance($applicationId, $payload), 'Clearance placeholder updated.');
    }

    public function eligibility(int $applicationId)
    {
        return $this->persist(static fn (): int => service('admissionAcceptance')->markEligible($applicationId), 'Applicant marked eligible for Block 5 student conversion.');
    }

    /** @param callable(array<string,mixed>):int|callable():int $callback */
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

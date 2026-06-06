<?php

namespace App\Controllers\Tenant\Admissions;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;
use InvalidArgumentException;

/** Phase 8 admissions operations console for outbox retries and audit visibility. */
class OperationsController extends BaseController
{
    use ApiResponseTrait;

    protected $helpers = ['form'];

    public function index(): string
    {
        return view('tenant/admissions/operations/index', service('admissionOperations')->overview());
    }

    public function retryOutbox(int $outboxId)
    {
        try {
            $id = service('admissionOperations')->retryOutbox($outboxId);
        } catch (InvalidArgumentException $exception) {
            return $this->request->isAJAX()
                ? $this->fail('Validation failed.', ['admissions' => $exception->getMessage()], 422)
                : redirect()->back()->with('errors', ['admissions' => $exception->getMessage()]);
        }

        return $this->request->isAJAX()
            ? $this->ok('Outbox retry scheduled.', ['id' => $id])
            : redirect()->back()->with('message', 'Outbox retry scheduled.');
    }
}

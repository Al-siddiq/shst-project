<?php

namespace App\Controllers\Tenant\Admissions;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;
use InvalidArgumentException;

/** Server-rendered and JSON endpoints for admission-cycle configuration. */
class CycleController extends BaseController
{
    use ApiResponseTrait;

    protected $helpers = ['form'];

    public function index(): string
    {
        return view('tenant/admissions/cycles', service('admissionConfiguration')->workspace());
    }

    public function save()
    {
        $payload = $this->payload();
        $rules = ['id' => 'permit_empty|integer', 'academic_session_id' => 'required|integer', 'title' => 'required|max_length[180]', 'code' => 'required|alpha_dash|max_length[60]', 'opens_at' => 'required|valid_date', 'closes_at' => 'required|valid_date', 'status' => 'required|in_list[draft,scheduled,open,closed,under_review,admission_published,archived]', 'is_public' => 'permit_empty|in_list[0,1]', 'allow_multiple_public_cycles' => 'permit_empty|in_list[0,1]', 'instructions' => 'permit_empty|max_length[20000]', 'screening_instructions' => 'permit_empty|max_length[20000]'];

        return $this->persist('saveCycle', $payload, $rules, 'Admission cycle saved.');
    }

    public function transition(int $id, string $status)
    {
        try {
            service('admissionConfiguration')->transitionCycle($id, $status);
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success('Admission cycle status updated.', ['id' => $id, 'status' => $status]);
    }

    /** @param array<string, mixed> $payload @param array<string, string> $rules */
    private function persist(string $method, array $payload, array $rules, string $message)
    {
        if (! $this->validateData($payload, $rules)) {
            return $this->validationError();
        }
        try {
            $id = service('admissionConfiguration')->{$method}($this->validator->getValidated());
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage());
        }

        return $this->success($message, ['id' => $id]);
    }

    /** @return array<string, mixed> */ private function payload(): array { return $this->wantsJson() ? ($this->request->getJSON(true) ?? []) : $this->request->getPost(); }
    private function success(string $message, array $data = []) { return $this->wantsJson() ? $this->ok($message, $data) : redirect()->back()->with('message', $message); }
    private function error(string $message) { return $this->wantsJson() ? $this->fail('Validation failed.', ['admissions' => $message], 422) : redirect()->back()->withInput()->with('errors', ['admissions' => $message]); }
    private function wantsJson(): bool { return $this->request->isAJAX() || str_contains((string) $this->request->getHeaderLine('Content-Type'), 'application/json'); }
    private function validationError() { return $this->wantsJson() ? $this->fail('Validation failed.', $this->validator->getErrors(), 422) : redirect()->back()->withInput()->with('errors', $this->validator->getErrors()); }
}

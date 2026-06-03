<?php

namespace App\Controllers\Tenant\Admissions;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;
use InvalidArgumentException;

/** Configures tenant programmes exposed within one admission cycle. */
class ProgrammeOpeningController extends BaseController
{
    use ApiResponseTrait;
    protected $helpers = ['form'];

    public function index(): string { return view('tenant/admissions/programmes', service('admissionConfiguration')->workspace()); }
    public function save()
    {
        $payload = $this->payload();
        $rules = ['id' => 'permit_empty|integer', 'admission_cycle_id' => 'required|integer', 'programme_id' => 'required|integer', 'department_id' => 'required|integer', 'entry_level_id' => 'permit_empty|integer', 'application_quota' => 'permit_empty|integer|greater_than_equal_to[0]', 'screening_method' => 'required|in_list[manual_review,physical_screening,online_screening,exam,interview,combined]', 'instructions' => 'permit_empty|max_length[20000]', 'requirement_summary' => 'permit_empty|max_length[20000]', 'status' => 'required|in_list[open,closed,hidden,full,suspended]', 'sort_order' => 'permit_empty|integer'];
        if (! $this->validateData($payload, $rules)) { return $this->failure($this->validator->getErrors()); }
        try { $id = service('admissionConfiguration')->saveProgrammeOpening($this->validator->getValidated()); }
        catch (InvalidArgumentException $e) { return $this->failure(['admissions' => $e->getMessage()]); }
        return $this->success('Admission programme opening saved.', ['id' => $id]);
    }
    /** @return array<string, mixed> */ private function payload(): array { return ($this->wantsJson()) ? ($this->request->getJSON(true) ?? []) : $this->request->getPost(); }
    private function success(string $message, array $data) { return $this->wantsJson() ? $this->ok($message, $data) : redirect()->back()->with('message', $message); }
    private function wantsJson(): bool { return $this->request->isAJAX() || str_contains((string) $this->request->getHeaderLine('Content-Type'), 'application/json'); }
    private function failure(array $errors) { return $this->wantsJson() ? $this->fail('Validation failed.', $errors, 422) : redirect()->back()->withInput()->with('errors', $errors); }
}

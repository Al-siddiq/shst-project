<?php

namespace App\Controllers\Tenant\Admissions;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;
use InvalidArgumentException;

/** Manages configured biodata, O'Level subject, and private document rules. */
class RequirementController extends BaseController
{
    use ApiResponseTrait;
    protected $helpers = ['form'];

    public function index(): string { return view('tenant/admissions/requirements', service('admissionConfiguration')->workspace()); }

    public function saveDefinition() { return $this->persist('saveRequirement', ['id' => 'permit_empty|integer', 'admission_cycle_id' => 'required|integer', 'programme_opening_id' => 'permit_empty|integer', 'requirement_type' => 'required|in_list[biodata,olevel,document,other]', 'code' => 'required|alpha_dash|max_length[80]', 'label' => 'required|max_length[180]', 'description' => 'permit_empty|max_length[10000]', 'is_required' => 'permit_empty|in_list[0,1]', 'configuration_json' => 'permit_empty|max_length[10000]', 'status' => 'required|in_list[active,archived]', 'sort_order' => 'permit_empty|integer'], 'Admission requirement saved.'); }
    public function saveSubject() { return $this->persist('saveSubjectRequirement', ['id' => 'permit_empty|integer', 'admission_cycle_id' => 'required|integer', 'programme_opening_id' => 'permit_empty|integer', 'subject_code' => 'required|alpha_dash|max_length[60]', 'subject_name' => 'required|max_length[160]', 'minimum_grade' => 'permit_empty|max_length[20]', 'requirement_group' => 'permit_empty|max_length[80]', 'is_required' => 'permit_empty|in_list[0,1]', 'status' => 'required|in_list[active,archived]', 'sort_order' => 'permit_empty|integer'], 'O Level subject requirement saved.'); }
    public function saveDocument() { return $this->persist('saveDocumentRequirement', ['id' => 'permit_empty|integer', 'admission_cycle_id' => 'required|integer', 'programme_opening_id' => 'permit_empty|integer', 'document_type' => 'required|alpha_dash|max_length[80]', 'label' => 'required|max_length[180]', 'allowed_mime_types' => 'required|max_length[500]', 'maximum_size_bytes' => 'required|integer|greater_than[0]', 'is_required' => 'permit_empty|in_list[0,1]', 'status' => 'required|in_list[active,archived]', 'sort_order' => 'permit_empty|integer'], 'Document requirement saved.'); }

    /** @param array<string, string> $rules */
    private function persist(string $method, array $rules, string $message)
    {
        $payload = ($this->wantsJson()) ? ($this->request->getJSON(true) ?? []) : $this->request->getPost();
        if (! $this->validateData($payload, $rules)) { return $this->failure($this->validator->getErrors()); }
        try { $id = service('admissionConfiguration')->{$method}($this->validator->getValidated()); }
        catch (InvalidArgumentException $e) { return $this->failure(['admissions' => $e->getMessage()]); }
        return $this->wantsJson() ? $this->ok($message, ['id' => $id]) : redirect()->back()->with('message', $message);
    }

    private function wantsJson(): bool { return $this->request->isAJAX() || str_contains((string) $this->request->getHeaderLine('Content-Type'), 'application/json'); }
    private function failure(array $errors) { return $this->wantsJson() ? $this->fail('Validation failed.', $errors, 422) : redirect()->back()->withInput()->with('errors', $errors); }
}

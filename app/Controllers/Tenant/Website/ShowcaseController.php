<?php

namespace App\Controllers\Tenant\Website;

use App\Controllers\BaseController;
use InvalidArgumentException;

/**
 * Tenant-admin form endpoints for Phase 2 showcase records.
 *
 * Forms remain server-rendered and narrow in scope. Mutation ownership,
 * publication authority, cache invalidation, and auditing stay in the service.
 */
class ShowcaseController extends BaseController
{
    protected $helpers = ['form'];

    public function departments(): string
    {
        return view('tenant/website/showcase/departments', service('showcaseManagement')->departmentFormData($this->selectedProfileId()));
    }

    public function saveDepartment()
    {
        $rules = $this->profileRules(['department_id' => 'required|integer']);
        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        return $this->save('saveDepartment', 'Department public profile saved.');
    }

    public function programmes(): string
    {
        return view('tenant/website/showcase/programmes', service('showcaseManagement')->programmeFormData($this->selectedProfileId()));
    }

    public function saveProgramme()
    {
        $rules = $this->profileRules([
            'programme_id' => 'required|integer',
            'entry_requirements' => 'permit_empty|max_length[10000]',
            'career_opportunities' => 'permit_empty|max_length[10000]',
            'duration_explanation' => 'permit_empty|max_length[255]',
            'award_type' => 'permit_empty|max_length[120]',
            'admission_status' => 'permit_empty|in_list[open,closed,not_specified]',
        ]);
        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        return $this->save('saveProgramme', 'Programme public profile saved.');
    }

    public function admissions(): string
    {
        return view('tenant/website/showcase/admissions', ['admissions' => service('showcaseManagement')->admissionFormData()]);
    }

    public function saveAdmissions()
    {
        $rules = [
            'title' => 'required|max_length[255]',
            'summary' => 'permit_empty|max_length[10000]',
            'body' => 'permit_empty|max_length[20000]',
            'admission_status' => 'required|in_list[open,closed]',
            'requirements_body' => 'permit_empty|max_length[20000]',
            'application_fee_note' => 'permit_empty|max_length[5000]',
            'screening_information' => 'permit_empty|max_length[10000]',
            'required_documents' => 'permit_empty|max_length[10000]',
            'important_dates' => 'permit_empty|max_length[10000]',
            'how_to_apply_body' => 'permit_empty|max_length[20000]',
            'application_link_label' => 'permit_empty|max_length[120]',
            'application_url' => 'permit_empty|max_length[500]',
            'seo_title' => 'permit_empty|max_length[255]',
            'seo_description' => 'permit_empty|max_length[320]',
            'status' => 'required|in_list[draft,published,archived]',
        ];
        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        return $this->save('saveAdmissions', 'Admission information saved.');
    }

    /** Returns a tenant-scoped profile selector for server-rendered edit links. */
    private function selectedProfileId(): ?int
    {
        $id = (int) $this->request->getGet('profile');

        return $id > 0 ? $id : null;
    }

    /** @param array<string, string> $specific @return array<string, string> */
    private function profileRules(array $specific): array
    {
        return array_merge($specific, [
            'slug' => 'required|regex_match[/^[a-z0-9-]+$/]|max_length[180]',
            'summary' => 'permit_empty|max_length[10000]',
            'body' => 'permit_empty|max_length[20000]',
            'featured_media_id' => 'permit_empty|integer',
            'seo_title' => 'permit_empty|max_length[255]',
            'seo_description' => 'permit_empty|max_length[320]',
            'status' => 'required|in_list[draft,published,archived]',
        ]);
    }

    private function save(string $method, string $message)
    {
        try {
            service('showcaseManagement')->{$method}($this->validator->getValidated());
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->with('errors', ['showcase' => $exception->getMessage()]);
        }

        return redirect()->back()->with('message', $message);
    }
}

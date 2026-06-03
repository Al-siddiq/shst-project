<?php

namespace App\Controllers\Applicant;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;
use InvalidArgumentException;

/** Starts, resumes, autosaves, previews, and submits applicant applications. */
class ApplicationController extends BaseController
{
    use ApiResponseTrait;
    protected $helpers = ['form'];

    public function start()
    {
        $payload = $this->payload();
        if (! $this->validateData($payload, ['programme_opening_id' => 'required|integer'])) {
            return $this->failure($this->validator->getErrors());
        }
        try {
            $draft = service('applicationDraft')->start((int) $this->validator->getValidated()['programme_opening_id']);
        } catch (InvalidArgumentException $exception) {
            return $this->failure(['application' => $exception->getMessage()]);
        }

        return $this->wantsJson() ? $this->ok('Draft application started.', ['token' => $draft['public_token']]) : redirect()->to(site_url('applicant/applications/' . $draft['public_token']))->with('message', 'Draft application started.');
    }

    public function show(string $token): string
    {
        $application = service('applicationDraft')->resume($token);
        return view('applicant/application', array_merge(service('publicWebsite')->page('apply', 'Draft application'), ['application' => $application, 'olevel' => service('olevelApplication')->forApplication((int) $application['id']), 'documents' => service('applicantDocument')->documentsForApplication((int) $application['id']), 'completion' => service('applicationCompletion')->evaluate($application)]));
    }

    public function saveBiodata(string $token)
    {
        $payload = $this->payload();
        $rules = ['surname' => 'permit_empty|max_length[120]', 'first_name' => 'permit_empty|max_length[120]', 'other_names' => 'permit_empty|max_length[160]', 'gender' => 'permit_empty|in_list[male,female,other]', 'date_of_birth' => 'permit_empty|valid_date', 'phone_e164' => 'permit_empty|max_length[40]', 'email' => 'permit_empty|valid_email|max_length[190]', 'residential_address' => 'permit_empty|max_length[500]', 'state_of_origin' => 'permit_empty|max_length[120]', 'lga_of_origin' => 'permit_empty|max_length[120]', 'nationality' => 'permit_empty|max_length[120]', 'marital_status' => 'permit_empty|max_length[40]', 'religion' => 'permit_empty|max_length[80]', 'next_of_kin_name' => 'permit_empty|max_length[180]', 'next_of_kin_phone_e164' => 'permit_empty|max_length[40]', 'guardian_name' => 'permit_empty|max_length[180]', 'guardian_phone_e164' => 'permit_empty|max_length[40]'];
        if (! $this->validateData($payload, $rules)) {
            return $this->failure($this->validator->getErrors());
        }
        try {
            $draft = service('applicationDraft')->saveBiodata($token, $this->validator->getValidated());
        } catch (InvalidArgumentException $exception) {
            return $this->failure(['biodata' => $exception->getMessage()]);
        }

        return $this->wantsJson() ? $this->ok('Biodata draft saved.', ['completion_percent' => $draft['biodata']['completion_percent'] ?? 0]) : redirect()->back()->with('message', 'Biodata draft saved.');
    }

    public function saveOlevel(string $token)
    {
        try {
            $olevel = service('olevelApplication')->save($token, $this->payload());
        } catch (InvalidArgumentException $exception) {
            return $this->failure(['olevel' => $exception->getMessage()]);
        }

        return $this->wantsJson() ? $this->ok("O'Level results saved.", $olevel) : redirect()->back()->with('message', "O'Level results saved.");
    }

    public function preview(string $token): string
    {
        return view('applicant/preview', array_merge(service('publicWebsite')->page('apply', 'Application preview'), service('applicationSubmission')->preview($token)));
    }

    public function submit(string $token)
    {
        try {
            $result = service('applicationSubmission')->submit($token);
        } catch (InvalidArgumentException $exception) {
            return $this->failure(['submission' => $exception->getMessage()]);
        }

        return $this->wantsJson() ? $this->ok('Application submitted.', $result) : redirect()->to(site_url('applicant/applications/' . $token . '/preview'))->with('message', 'Application submitted. Reference: ' . $result['application_number']);
    }

    /** @return array<string, mixed> */ private function payload(): array { return $this->wantsJson() ? ($this->request->getJSON(true) ?? []) : $this->request->getPost(); }
    private function wantsJson(): bool { return $this->request->isAJAX() || str_contains((string) $this->request->getHeaderLine('Content-Type'), 'application/json'); }
    private function failure(array $errors) { return $this->wantsJson() ? $this->fail('Validation failed.', $errors, 422) : redirect()->back()->withInput()->with('errors', $errors); }
}

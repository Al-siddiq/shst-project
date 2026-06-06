<?php

namespace App\Controllers\Applicant;

use App\Controllers\BaseController;
use InvalidArgumentException;

/** Creates or updates the tenant applicant access profile after Shield login. */
class ProfileController extends BaseController
{
    protected $helpers = ['form'];

    public function edit(): string
    {
        return view('applicant/profile', array_merge(service('publicWebsite')->page('apply', 'Applicant profile'), [
            'profile' => service('applicantAccessPolicy')->currentProfile(service('tenantContextManager')->current()),
        ]));
    }

    public function save()
    {
        $payload = $this->request->getPost();
        if (! $this->validateData($payload, ['identifier' => 'required|max_length[190]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        try {
            service('applicantProfile')->ensureProfile($this->validator->getValidated()['identifier']);
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->with('errors', ['identifier' => $exception->getMessage()]);
        }

        return redirect()->to(site_url('applicant'))->with('message', 'Applicant profile is ready.');
    }
}

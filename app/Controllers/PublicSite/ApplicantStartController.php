<?php

namespace App\Controllers\PublicSite;

use App\Controllers\BaseController;

/**
 * Tenant-resolved applicant entry page.
 *
 * Phase 0 deliberately does not create applications. It proves that every
 * future applicant journey begins from an active resolved tenant website.
 */
class ApplicantStartController extends BaseController
{
    protected $helpers = ['public_website'];

    public function index(): string
    {
        $data = service('publicWebsite')->page('apply', 'Apply');

        return view('public_site/apply', $data);
    }
}

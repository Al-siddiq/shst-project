<?php

namespace App\Controllers\PublicSite;

use App\Controllers\BaseController;

/** Displays admission guidance only; this controller intentionally has no POST action. */
class AdmissionController extends BaseController
{
    protected $helpers = ['public_website'];

    public function index(): string
    {
        $data = service('publicWebsite')->page('admissions', 'Admissions');
        $data['admissions'] = service('publicShowcase')->admissionInformation();
        // Block 2 guidance stays available while Block 3 contributes live,
        // tenant-scoped admission-cycle and programme-opening discovery.
        $data['activeCycle'] = service('publicAdmissions')->activeCycle();
        $data['openProgrammes'] = service('publicAdmissions')->openProgrammes();
        if ($data['admissions'] !== null) {
            $data['metaTitle'] = ($data['admissions']['seo_title'] ?: $data['admissions']['title']) . ' | ' . $data['settings']['site_title'];
            $data['metaDescription'] = $data['admissions']['seo_description'] ?: ($data['admissions']['summary'] ?? '');
        }

        return view('public_site/admissions', $data);
    }
}

<?php

namespace App\Controllers\PublicSite;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

/** Public read-only discovery for programmes opened by the resolved tenant. */
class AdmissionProgrammeController extends BaseController
{
    protected $helpers = ['public_website'];

    public function index(): string
    {
        $data = service('publicWebsite')->page('admission_programmes', 'Open admission programmes');
        $data['cycle'] = service('publicAdmissions')->activeCycle();
        $data['programmes'] = service('publicAdmissions')->openProgrammes();

        return view('public_site/admission_programmes', $data);
    }

    public function show(int $id): string
    {
        $programme = service('publicAdmissions')->programme($id);
        if ($programme === null) {
            throw PageNotFoundException::forPageNotFound('Admission programme was not found.');
        }
        $data = service('publicWebsite')->page('admission_programme', $programme['programme_name'], [$id]);
        $data['programme'] = $programme;

        return view('public_site/admission_programme', $data);
    }
}

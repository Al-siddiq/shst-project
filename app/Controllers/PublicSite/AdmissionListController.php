<?php

namespace App\Controllers\PublicSite;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use InvalidArgumentException;

/** Public Phase 6 admission-list pages expose safe fields only. */
class AdmissionListController extends BaseController
{
    public function index(): string
    {
        return view('public_site/admission_lists', array_merge(service('publicWebsite')->page('admissions', 'Admission lists'), ['publications' => service('admissionListPublication')->publicLists()]));
    }

    public function show(string $token): string
    {
        try {
            $data = service('admissionListPublication')->publicList($token);
        } catch (InvalidArgumentException) {
            throw PageNotFoundException::forPageNotFound('Admission list was not found.');
        }

        return view('public_site/admission_list', array_merge(service('publicWebsite')->page('admissions', 'Admission list'), $data));
    }
}

<?php

namespace App\Controllers\PublicSite;

use App\Controllers\BaseController;

/** Renders standalone public leadership profiles, never future private staff records. */
class ManagementController extends BaseController
{
    protected $helpers = ['public_website'];

    public function index(): string
    {
        $data = service('publicWebsite')->page('management', 'Management');
        $data['profiles'] = service('publicInstitutionalShowcase')->management();

        return view('public_site/management', $data);
    }
}

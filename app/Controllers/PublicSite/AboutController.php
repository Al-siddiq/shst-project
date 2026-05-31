<?php

namespace App\Controllers\PublicSite;

use App\Controllers\BaseController;

/** Renders public institutional copy without querying from the template. */
class AboutController extends BaseController
{
    protected $helpers = ['public_website'];

    public function index(): string
    {
        return view('public_site/about', service('publicWebsite')->page('about', 'About Us'));
    }
}

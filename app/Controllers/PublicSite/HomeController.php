<?php

namespace App\Controllers\PublicSite;

use App\Controllers\BaseController;

/** Renders the tenant-branded public landing page from prepared service data. */
class HomeController extends BaseController
{
    protected $helpers = ['public_website'];

    public function index(): string
    {
        return view('public_site/home', service('publicWebsite')->page('home'));
    }
}

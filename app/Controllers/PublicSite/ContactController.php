<?php

namespace App\Controllers\PublicSite;

use App\Controllers\BaseController;

/** Renders tenant-configured contact channels and profile fallbacks. */
class ContactController extends BaseController
{
    protected $helpers = ['public_website'];

    public function index(): string
    {
        return view('public_site/contact', service('publicWebsite')->page('contact', 'Contact Us'));
    }
}

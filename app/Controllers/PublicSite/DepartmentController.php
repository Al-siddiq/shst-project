<?php

namespace App\Controllers\PublicSite;

use App\Controllers\BaseController;
use InvalidArgumentException;

/** Renders published tenant departments and their published programmes. */
class DepartmentController extends BaseController
{
    protected $helpers = ['public_website'];

    public function index(): string
    {
        $data = service('publicWebsite')->page('departments', 'Departments');
        $data['departments'] = service('publicShowcase')->departments();

        return view('public_site/departments/index', $data);
    }

    public function show(string $slug)
    {
        try {
            $department = service('publicShowcase')->department($slug);
        } catch (InvalidArgumentException) {
            return $this->response->setStatusCode(404)->setBody(view('public_site/unavailable'));
        }

        $data = service('publicWebsite')->page('department', $department['name'], [$department['slug']]);
        $data['department'] = $department;
        $data['metaTitle'] = ($department['seo_title'] ?: $department['name']) . ' | ' . $data['settings']['site_title'];
        $data['metaDescription'] = $department['seo_description'] ?: ($department['summary'] ?? '');

        return view('public_site/departments/show', $data);
    }
}

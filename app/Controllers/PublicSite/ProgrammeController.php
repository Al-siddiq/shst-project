<?php

namespace App\Controllers\PublicSite;

use App\Controllers\BaseController;
use InvalidArgumentException;

/** Renders published tenant programmes with an optional department filter. */
class ProgrammeController extends BaseController
{
    protected $helpers = ['public_website'];

    public function index(): string
    {
        $departmentSlug = trim((string) $this->request->getGet('department')) ?: null;
        try {
            $programmes = service('publicShowcase')->programmes($departmentSlug);
        } catch (InvalidArgumentException) {
            $programmes = [];
        }

        $data = service('publicWebsite')->page('programmes', 'Programmes');
        $data['programmes'] = $programmes;
        $data['departments'] = service('publicShowcase')->departments();
        $data['selectedDepartment'] = $departmentSlug;

        return view('public_site/programmes/index', $data);
    }

    public function show(string $slug)
    {
        try {
            $programme = service('publicShowcase')->programme($slug);
        } catch (InvalidArgumentException) {
            return $this->response->setStatusCode(404)->setBody(view('public_site/unavailable'));
        }

        $data = service('publicWebsite')->page('programme', $programme['name'], [$programme['slug']]);
        $data['programme'] = $programme;
        $data['metaTitle'] = ($programme['seo_title'] ?: $programme['name']) . ' | ' . $data['settings']['site_title'];
        $data['metaDescription'] = $programme['seo_description'] ?: ($programme['summary'] ?? '');

        return view('public_site/programmes/show', $data);
    }
}

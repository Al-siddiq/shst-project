<?php

namespace App\Controllers\Tenant\Website;

use App\Controllers\BaseController;
use InvalidArgumentException;

/**
 * Validates website settings request shape while the service owns write policy.
 *
 * Keeping URL and media policy inside the service protects future JSON callers;
 * these rules provide immediate form feedback for the server-rendered CMS.
 */
class SettingsController extends BaseController
{
    protected $helpers = ['form'];

    public function index(): string
    {
        return view('tenant/website/settings', service('websiteSettingsManagement')->formData());
    }

    public function save()
    {
        $rules = [
            'site_title' => 'permit_empty|max_length[200]', 'tagline' => 'permit_empty|max_length[255]', 'motto' => 'permit_empty|max_length[255]',
            'hero_title' => 'permit_empty|max_length[255]', 'hero_summary' => 'permit_empty|max_length[10000]', 'hero_media_id' => 'permit_empty|integer',
            'about_summary' => 'permit_empty|max_length[10000]', 'about_body' => 'permit_empty|max_length[30000]', 'mission' => 'permit_empty|max_length[10000]',
            'vision' => 'permit_empty|max_length[10000]', 'history' => 'permit_empty|max_length[30000]', 'contact_email' => 'permit_empty|valid_email|max_length[190]',
            'contact_phone' => 'permit_empty|max_length[60]', 'contact_phone_alt' => 'permit_empty|max_length[60]', 'address' => 'permit_empty|max_length[255]',
            'map_embed_url' => 'permit_empty|max_length[500]', 'portal_url' => 'permit_empty|max_length[500]', 'application_info_url' => 'permit_empty|max_length[500]',
            'application_cta_label' => 'permit_empty|max_length[120]', 'is_public_enabled' => 'permit_empty|in_list[0,1]', 'seo_title' => 'permit_empty|max_length[255]',
            'seo_description' => 'permit_empty|max_length[320]', 'facebook_url' => 'permit_empty|max_length[500]', 'instagram_url' => 'permit_empty|max_length[500]',
            'x_url' => 'permit_empty|max_length[500]', 'youtube_url' => 'permit_empty|max_length[500]',
        ];
        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        try {
            service('websiteSettingsManagement')->save($this->validator->getValidated());
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->with('errors', ['settings' => $exception->getMessage()]);
        }

        return redirect()->back()->with('message', 'Website settings saved.');
    }
}

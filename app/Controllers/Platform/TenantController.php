<?php

namespace App\Controllers\Platform;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;
use InvalidArgumentException;

class TenantController extends BaseController
{
    use ApiResponseTrait;

    public function index()
    {
        if ($this->request->isAJAX() || $this->request->getHeaderLine('Accept') === 'application/json') {
            return $this->ok('Tenant list loaded.', ['items' => service('platformTenant')->index()]);
        }
        return view('platform/tenants/index', ['tenants' => service('platformTenant')->index()]);
    }

    public function show(int $tenantId)
    {
        try { return view('platform/tenants/show', service('platformTenant')->detail($tenantId)); }
        catch (InvalidArgumentException) { return redirect()->to(site_url('platform/tenants'))->with('errors', ['Tenant was not found.']); }
    }

    public function create()
    {
        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        if (! $this->validateData($payload, ['school_name' => 'required|min_length[3]|max_length[200]', 'short_name' => 'permit_empty|max_length[120]', 'slug' => 'required|alpha_dash|min_length[3]|max_length[150]|is_unique[tenants.slug]', 'official_email' => 'permit_empty|valid_email', 'official_phone' => 'permit_empty|max_length[50]'])) {
            return $this->formFailure($this->validator->getErrors());
        }
        $id = service('platformTenant')->create($this->validator->getValidated());
        return $this->request->isAJAX() ? $this->ok('Tenant created.', ['tenant_id' => $id], 201) : redirect()->to(site_url('platform/tenants/' . $id))->with('message', 'Tenant created in pending setup.');
    }

    public function update(int $tenantId)
    {
        $payload = $this->request->getPost();
        if (! $this->validateData($payload, ['school_name' => 'required|min_length[3]|max_length[200]', 'short_name' => 'permit_empty|max_length[120]', 'slug' => 'required|alpha_dash|min_length[3]|max_length[150]', 'official_email' => 'permit_empty|valid_email', 'official_phone' => 'permit_empty|max_length[50]'])) return $this->formFailure($this->validator->getErrors());
        try { service('platformTenant')->update($tenantId, $this->validator->getValidated()); }
        catch (InvalidArgumentException $e) { return $this->formFailure(['tenant' => $e->getMessage()]); }
        return redirect()->back()->with('message', 'Tenant profile updated.');
    }

    public function transition(int $tenantId)
    {
        try { service('platformTenant')->transition($tenantId, (string) $this->request->getPost('status'), (string) $this->request->getPost('reason')); }
        catch (InvalidArgumentException $e) { return $this->formFailure(['lifecycle' => $e->getMessage()]); }
        return redirect()->back()->with('message', 'Tenant lifecycle updated.');
    }

    public function saveDomain(int $tenantId)
    {
        try { service('platformTenant')->saveDomain($tenantId, $this->request->getPost()); }
        catch (InvalidArgumentException $e) { return $this->formFailure(['domain' => $e->getMessage()]); }
        return redirect()->back()->with('message', 'Tenant domain saved.');
    }

    private function formFailure(array $errors)
    {
        if ($this->request->isAJAX()) return $this->fail('Validation failed.', $errors, 422);
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}

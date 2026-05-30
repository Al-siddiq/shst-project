<?php

namespace App\Controllers\Platform;

use App\Controllers\BaseController;
use App\Models\TenantModel;
use App\Traits\ApiResponseTrait;

class TenantController extends BaseController
{
    use ApiResponseTrait;

    /**
     * Phase 2 skeleton: list platform-managed tenants.
     */
    public function index()
    {
        $tenants = (new TenantModel())->orderBy('id', 'DESC')->findAll();

        return $this->ok('Tenant list loaded.', ['items' => $tenants]);
    }

    /**
     * Phase 2 skeleton: create tenant in pending_setup state.
     */
    public function create()
    {
        $rules = [
            'school_name' => 'required|min_length[3]|max_length[200]',
            'slug' => 'required|alpha_dash|min_length[3]|max_length[150]|is_unique[tenants.slug]',
            'status' => 'permit_empty|in_list[pending_setup,active,suspended,deactivated,archived]',
        ];

        if (! $this->validateData($this->request->getJSON(true) ?? [], $rules)) {
            return $this->fail('Validation failed.', $this->validator->getErrors(), 422);
        }

        $payload = $this->validator->getValidated();
        $payload['status'] ??= 'pending_setup';

        $id = (new TenantModel())->insert($payload, true);
        service('auditLogger')->record('platform.tenant.create', [
            'tenant_id' => $id,
            'context' => 'platform',
            'target_type' => 'tenant',
            'target_id' => $id,
            'summary' => 'Platform tenant created.',
        ]);

        return $this->ok('Tenant created.', ['tenant_id' => $id], 201);
    }
}

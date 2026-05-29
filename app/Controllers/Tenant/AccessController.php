<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\MembershipAuthorityModel;
use App\Models\OperationalAuthorityModel;
use App\Models\TenantIamGroupAssignmentModel;
use App\Models\TenantMembershipModel;
use App\Traits\ApiResponseTrait;

class AccessController extends BaseController
{
    use ApiResponseTrait;

    public function createMembership()
    {
        $payload = $this->request->getJSON(true) ?? [];
        $rules = [
            'user_id' => 'required|integer',
            'status' => 'permit_empty|in_list[active,pending,suspended,revoked]',
            'membership_label' => 'permit_empty|max_length[120]',
        ];

        if (! $this->validateData($payload, $rules)) {
            return $this->fail('Validation failed.', $this->validator->getErrors(), 422);
        }

        $payload['status'] ??= 'active';
        $payload['is_active'] = $payload['status'] === 'active' ? 1 : 0;

        $id = (new TenantMembershipModel())->insert($this->withTenant($payload), true);

        return $this->ok('Tenant membership created.', ['id' => $id], 201);
    }

    public function assignGroup()
    {
        $payload = $this->request->getJSON(true) ?? [];
        $rules = ['user_id' => 'required|integer', 'group_name' => 'required|alpha_dash|max_length[80]'];

        if (! $this->validateData($payload, $rules)) {
            return $this->fail('Validation failed.', $this->validator->getErrors(), 422);
        }

        if (! service('tenantAccess')->isMember(service('tenantContextManager')->current(), (int) $payload['user_id'])) {
            return $this->fail('Validation failed.', ['user_id' => 'User must be an active tenant member before group assignment.'], 422);
        }

        $id = (new TenantIamGroupAssignmentModel())->insert($payload, true);

        return $this->ok('Tenant IAM group assigned.', ['id' => $id], 201);
    }

    public function createAuthority()
    {
        $payload = $this->request->getJSON(true) ?? [];
        $rules = ['code' => 'required|alpha_dash|max_length[120]', 'name' => 'required|max_length[160]'];

        if (! $this->validateData($payload, $rules)) {
            return $this->fail('Validation failed.', $this->validator->getErrors(), 422);
        }

        $id = (new OperationalAuthorityModel())->insert($payload, true);

        return $this->ok('Operational authority created.', ['id' => $id], 201);
    }

    public function grantAuthority()
    {
        $payload = $this->request->getJSON(true) ?? [];
        $rules = [
            'user_id' => 'required|integer',
            'authority_id' => 'required|integer',
            'scope_type' => 'permit_empty|alpha_dash|max_length[80]',
            'scope_id' => 'permit_empty|integer',
        ];

        if (! $this->validateData($payload, $rules)) {
            return $this->fail('Validation failed.', $this->validator->getErrors(), 422);
        }

        $context = service('tenantContextManager')->current();
        if (! service('tenantAccess')->isMember($context, (int) $payload['user_id'])) {
            return $this->fail('Validation failed.', ['user_id' => 'User must be an active tenant member before authority grant.'], 422);
        }
        if ((new OperationalAuthorityModel())->find((int) $payload['authority_id']) === null) {
            return $this->fail('Validation failed.', ['authority_id' => 'Invalid tenant authority reference.'], 422);
        }

        $id = (new MembershipAuthorityModel())->insert($payload, true);

        return $this->ok('Operational authority granted.', ['id' => $id], 201);
    }

    public function navigation()
    {
        return $this->ok('Navigation resolved.', ['items' => service('navigationResolver')->tenantItems()]);
    }

    private function withTenant(array $payload): array
    {
        $context = service('tenantContextManager')->current();
        $payload['tenant_id'] = $context->tenantId;

        return $payload;
    }
}

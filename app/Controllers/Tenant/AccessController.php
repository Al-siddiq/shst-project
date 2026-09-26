<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\MembershipAuthorityModel;
use App\Models\OperationalAuthorityModel;
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

        if (($payload['status'] ?? 'active') !== 'active') {
            return $this->fail('Validation failed.', ['status' => 'New permanent bindings must begin active.'], 422);
        }

        try {
            $membership = service('tenantIdentity')->bindUser(
                (int) $payload['user_id'],
                service('tenantContextManager')->current(),
                (string) ($payload['membership_label'] ?? 'Tenant account')
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->fail('Membership could not be created.', ['membership' => $exception->getMessage()], 422);
        }

        return $this->ok('Tenant membership created.', ['id' => $membership['id']], 201);
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

        return $this->fail(
            'Legacy tenant IAM group writes are retired.',
            ['group_name' => 'Assign the single primary broad group through Shield account provisioning.'],
            410
        );
    }

    public function createAuthority()
    {
        $payload = $this->request->getJSON(true) ?? [];
        $rules = ['code' => 'required|regex_match[/^[a-z0-9._-]+$/]|max_length[120]', 'name' => 'required|max_length[160]'];

        if (! $this->validateData($payload, $rules)) {
            return $this->fail('Validation failed.', $this->validator->getErrors(), 422);
        }

        $id = (new OperationalAuthorityModel())->insert($payload, true);
        $this->audit('tenant.operational_authority.create', 'operational_authority', $id);

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
        $this->audit('tenant.operational_authority.grant', 'membership_authority', $id);

        return $this->ok('Operational authority granted.', ['id' => $id], 201);
    }

    public function navigation()
    {
        return $this->ok('Navigation resolved.', ['items' => service('navigationResolver')->tenantItems()]);
    }

    private function audit(string $action, string $targetType, int|string $targetId): void
    {
        service('auditLogger')->record($action, [
            'target_type' => $targetType,
            'target_id' => $targetId,
            'summary' => 'Tenant access configuration changed.',
        ]);
    }
}

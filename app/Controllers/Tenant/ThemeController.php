<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\DepartmentIdentityModel;
use App\Models\Tenant\DepartmentModel;
use App\Models\TenantThemeModel;
use App\Traits\ApiResponseTrait;

class ThemeController extends BaseController
{
    use ApiResponseTrait;

    public function show()
    {
        return $this->ok('Theme resolved.', [
            'theme' => service('themeResolver')->tenantTheme(),
            'department_identities' => service('themeResolver')->departmentIdentities(),
        ]);
    }

    public function upsertTheme()
    {
        $payload = $this->request->getJSON(true) ?? [];
        $rules = [
            'primary_color' => 'permit_empty|regex_match[/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/]',
            'secondary_color' => 'permit_empty|regex_match[/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/]',
            'accent_color' => 'permit_empty|regex_match[/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/]',
            'logo_path' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validateData($payload, $rules)) {
            return $this->fail('Validation failed.', $this->validator->getErrors(), 422);
        }

        $model = new TenantThemeModel();
        $existing = $model->first();

        if ($existing) {
            $model->update($existing['id'], $payload);
            $id = $existing['id'];
            $message = 'Tenant theme updated.';
        } else {
            $id = $model->insert($payload, true);
            $message = 'Tenant theme created.';
        }

        service('auditLogger')->record('tenant.theme.upsert', [
            'target_type' => 'tenant_theme',
            'target_id' => $id,
            'summary' => $message,
            'metadata' => ['fields' => array_keys($payload)],
        ]);

        return $this->ok($message, ['id' => $id], $existing ? 200 : 201);
    }

    public function upsertDepartmentIdentity()
    {
        $payload = $this->request->getJSON(true) ?? [];
        $rules = [
            'department_id' => 'required|integer',
            'color_hex' => 'required|regex_match[/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/]',
            'icon_key' => 'permit_empty|alpha_dash|max_length[80]',
        ];

        if (! $this->validateData($payload, $rules)) {
            return $this->fail('Validation failed.', $this->validator->getErrors(), 422);
        }

        if ((new DepartmentModel())->find((int) $payload['department_id']) === null) {
            return $this->fail('Validation failed.', ['department_id' => 'Invalid tenant department reference.'], 422);
        }

        $model = new DepartmentIdentityModel();
        $existing = $model->where('department_id', (int) $payload['department_id'])->first();

        if ($existing) {
            $model->update($existing['id'], $payload);
            $id = $existing['id'];
            $message = 'Department identity updated.';
        } else {
            $id = $model->insert($payload, true);
            $message = 'Department identity created.';
        }

        service('auditLogger')->record('department.identity.upsert', [
            'target_type' => 'department_identity',
            'target_id' => $id,
            'summary' => $message,
            'metadata' => ['department_id' => $payload['department_id']],
        ]);

        return $this->ok($message, ['id' => $id], $existing ? 200 : 201);
    }
}

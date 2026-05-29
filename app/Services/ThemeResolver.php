<?php

namespace App\Services;

use App\Models\DepartmentIdentityModel;
use App\Models\TenantThemeModel;

class ThemeResolver
{
    private const DEFAULT_THEME = [
        'primary_color' => '#0f766e',
        'secondary_color' => '#134e4a',
        'accent_color' => '#f59e0b',
        'logo_path' => null,
    ];

    public function tenantTheme(): array
    {
        $context = service('tenantContextManager')->current();
        if (! $context->isResolved()) {
            return self::DEFAULT_THEME;
        }

        $theme = (new TenantThemeModel())->first();

        return array_merge(self::DEFAULT_THEME, $theme ?? []);
    }

    public function departmentIdentities(): array
    {
        $context = service('tenantContextManager')->current();
        if (! $context->isResolved()) {
            return [];
        }

        return (new DepartmentIdentityModel())->findAll();
    }
}

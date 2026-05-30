<?php

namespace App\Services;

class LayoutResolver
{
    public function layoutFor(string $portal): string
    {
        return match ($portal) {
            'platform' => 'layouts/platform_admin',
            'lecturer' => 'layouts/lecturer',
            'student' => 'layouts/student',
            default => 'layouts/tenant_admin',
        };
    }

    public function payload(string $portal): array
    {
        return [
            'layout' => $this->layoutFor($portal),
            'theme' => service('themeResolver')->tenantTheme(),
            'navigation' => $portal === 'tenant' ? service('navigationResolver')->tenantItems() : [],
        ];
    }
}

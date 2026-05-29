<?php

namespace App\Services;

use Config\Navigation;

class NavigationResolver
{
    public function __construct(private readonly TenantAccessService $access = new TenantAccessService())
    {
    }

    public function tenantItems(): array
    {
        $context = service('tenantContextManager')->current();
        $groups = $this->access->groups($context);
        $authorities = $this->access->authorities($context);

        return array_values(array_filter(
            config(Navigation::class)->tenantItems,
            static function (array $item) use ($groups, $authorities): bool {
                $requiredGroups = $item['groups'] ?? [];
                $requiredAuthorities = $item['authorities'] ?? [];

                $groupAllowed = $requiredGroups === [] || array_intersect($requiredGroups, $groups) !== [];
                $authorityAllowed = $requiredAuthorities === [] || array_intersect($requiredAuthorities, $authorities) !== [];

                return $groupAllowed && $authorityAllowed;
            }
        ));
    }
}

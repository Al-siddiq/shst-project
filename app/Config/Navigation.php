<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Navigation extends BaseConfig
{
    /**
     * Application navigation definitions. These are not tenant-specific data;
     * they are product capabilities resolved centrally for each user's access.
     */
    public array $tenantItems = [
        [
            'label' => 'Dashboard',
            'route' => '/internal/dashboard',
            'groups' => ['tenant_super_admin', 'tenant_admin', 'lecturer', 'student'],
            'authorities' => [],
        ],
        [
            'label' => 'School Configuration',
            'route' => '/tenant/config/profile',
            'groups' => ['tenant_super_admin', 'tenant_admin'],
            'authorities' => ['school.configuration.manage'],
        ],
        [
            'label' => 'Access Control',
            'route' => '/tenant/access/memberships',
            'groups' => ['tenant_super_admin', 'tenant_admin'],
            'authorities' => ['tenant.access.manage'],
        ],
    ];
}

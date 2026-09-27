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
            'route' => '/tenant/admin',
            'groups' => ['tenant_super_admin', 'tenant_admin'],
            'authorities' => ['school.configuration.manage'],
        ],
        [
            'label' => 'School Configuration',
            'route' => '/tenant/admin/configuration',
            'groups' => ['tenant_super_admin', 'tenant_admin'],
            'authorities' => ['school.configuration.manage'],
        ],
        [
            'label' => 'Access Control',
            'route' => '/tenant/admin/access',
            'groups' => ['tenant_super_admin'],
            'authorities' => ['tenant.access.manage'],
        ],
        ['label' => 'Branding', 'route' => '/tenant/admin/branding', 'groups' => ['tenant_super_admin', 'tenant_admin'], 'authorities' => ['school.configuration.manage']],
        ['label' => 'Domains', 'route' => '/tenant/admin/domains', 'groups' => ['tenant_super_admin', 'tenant_admin'], 'authorities' => ['school.configuration.manage']],
        ['label' => 'Website & CMS', 'route' => '/tenant/website', 'groups' => ['tenant_super_admin', 'tenant_admin', 'lecturer'], 'authorities' => ['website.dashboard.view']],
        ['label' => 'Admissions', 'route' => '/tenant/admissions', 'groups' => ['tenant_super_admin', 'tenant_admin', 'lecturer'], 'authorities' => ['admissions.dashboard.view']],
        ['label' => 'Audit', 'route' => '/tenant/admin/audit', 'groups' => ['tenant_super_admin'], 'authorities' => ['tenant.access.manage']],
    ];
}

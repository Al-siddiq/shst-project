<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

/**
 * Shield groups are broad identity classes only. Temporary responsibilities
 * such as admission officer remain tenant operational authorities.
 */
class AuthGroups extends ShieldAuthGroups
{
    /** New self-service registrations begin as applicants, never staff. */
    public string $defaultGroup = 'applicant';

    /** @var array<string, array{title: string, description: string}> */
    public array $groups = [
        'platform_admin' => ['title' => 'Platform Admin', 'description' => 'Platform-level SaaS administrator.'],
        'tenant_super_admin' => ['title' => 'Tenant Super Admin', 'description' => 'Tenant-level super administrator.'],
        'tenant_admin' => ['title' => 'Tenant Admin', 'description' => 'Tenant administrator.'],
        'lecturer' => ['title' => 'Lecturer', 'description' => 'Teaching identity class.'],
        'student' => ['title' => 'Student', 'description' => 'Student identity class.'],
        'applicant' => ['title' => 'Applicant', 'description' => 'Applicant portal identity class.'],
    ];

    /** @var array<string, string> */
    public array $permissions = [];

    /** @var array<string, list<string>> */
    public array $matrix = [];
}

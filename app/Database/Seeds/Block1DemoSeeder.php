<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Block1DemoSeeder extends Seeder
{
    /**
     * Demo actor IDs used by tenant memberships, IAM groups, and authority grants.
     *
     * These IDs intentionally do not create auth users because Shield owns identity
     * tables. In a local Shield-enabled setup, create/match users with these IDs
     * or update the seeded memberships after creating real test identities.
     */
    private const USERS = [
        'platform_admin' => 1,
        'tenant_admin' => 2,
        'lecturer' => 3,
        'student' => 4,
        'second_tenant_admin' => 5,
    ];

    private string $now;

    public function run(): void
    {
        $this->now = date('Y-m-d H:i:s');

        $this->db->transStart();

        $alphaTenant = $this->seedTenant([
            'school_name' => 'Demo School of Health Technology, Lagos',
            'short_name' => 'Demo SHT Lagos',
            'slug' => 'demo-sht-lagos',
            'official_email' => 'info@demo-sht-lagos.test',
            'official_phone' => '+2348010001000',
            'status' => 'active',
            'subscription_status' => 'trial',
        ]);

        $betaTenant = $this->seedTenant([
            'school_name' => 'Sample College of Health Sciences, Kano',
            'short_name' => 'Sample CHS Kano',
            'slug' => 'sample-chs-kano',
            'official_email' => 'info@sample-chs-kano.test',
            'official_phone' => '+2348020002000',
            'status' => 'active',
            'subscription_status' => 'trial',
        ]);

        $this->seedTenantDataset($alphaTenant, 'demo-sht-lagos', [
            'state' => 'Lagos',
            'lga' => 'Ikeja',
            'primary_color' => '#0f766e',
            'secondary_color' => '#134e4a',
            'accent_color' => '#f59e0b',
        ]);

        $this->seedTenantDataset($betaTenant, 'sample-chs-kano', [
            'state' => 'Kano',
            'lga' => 'Nassarawa',
            'primary_color' => '#1d4ed8',
            'secondary_color' => '#1e3a8a',
            'accent_color' => '#f97316',
        ]);

        $this->db->transComplete();
    }

    private function seedTenant(array $tenant): int
    {
        return $this->findOrInsert('tenants', ['slug' => $tenant['slug']], $tenant + $this->timestamps());
    }

    private function seedTenantDataset(int $tenantId, string $slug, array $profile): void
    {
        $this->seedDomains($tenantId, $slug);
        $this->seedProfile($tenantId, $slug, $profile);
        $this->seedTheme($tenantId, $profile);
        $this->seedMemberships($tenantId, $slug);
        $this->seedAcademicStructure($tenantId, $slug, $profile);
        $this->seedAuthorities($tenantId, $slug);
        $this->seedAuditLogs($tenantId, $slug);
    }

    private function seedDomains(int $tenantId, string $slug): void
    {
        foreach ([
            ['domain' => $slug . '.shst.test', 'type' => 'subdomain', 'is_primary' => 1],
            ['domain' => 'portal.' . $slug . '.test', 'type' => 'custom_domain', 'is_primary' => 0],
        ] as $domain) {
            $this->findOrInsert('tenant_domains', ['domain' => $domain['domain']], [
                'tenant_id' => $tenantId,
                'domain' => $domain['domain'],
                'type' => $domain['type'],
                'is_primary' => $domain['is_primary'],
                'status' => 'active',
            ] + $this->timestamps());
        }
    }

    private function seedProfile(int $tenantId, string $slug, array $profile): void
    {
        $name = $slug === 'demo-sht-lagos'
            ? 'Demo School of Health Technology, Lagos'
            : 'Sample College of Health Sciences, Kano';

        $this->findOrInsert('tenant_profiles', ['tenant_id' => $tenantId], [
            'tenant_id' => $tenantId,
            'institution_name' => $name,
            'short_name' => $slug === 'demo-sht-lagos' ? 'Demo SHT Lagos' : 'Sample CHS Kano',
            'official_email' => 'info@' . $slug . '.test',
            'official_phone' => $slug === 'demo-sht-lagos' ? '+2348010001000' : '+2348020002000',
            'address' => $slug === 'demo-sht-lagos' ? '1 Health Avenue, Ikeja' : '20 College Road, Nassarawa',
            'state' => $profile['state'],
            'lga' => $profile['lga'],
        ] + $this->timestamps());
    }

    private function seedTheme(int $tenantId, array $profile): void
    {
        $this->findOrInsert('tenant_themes', ['tenant_id' => $tenantId], [
            'tenant_id' => $tenantId,
            'primary_color' => $profile['primary_color'],
            'secondary_color' => $profile['secondary_color'],
            'accent_color' => $profile['accent_color'],
            'logo_path' => null,
        ] + $this->timestamps());
    }

    private function seedMemberships(int $tenantId, string $slug): void
    {
        $users = $slug === 'demo-sht-lagos'
            ? [
                self::USERS['tenant_admin'] => ['Tenant Administrator', 'tenant_admin'],
                self::USERS['lecturer'] => ['Lecturer Placeholder', 'lecturer'],
                self::USERS['student'] => ['Student Placeholder', 'student'],
            ]
            : [
                self::USERS['second_tenant_admin'] => ['Second Tenant Administrator', 'tenant_admin'],
            ];

        foreach ($users as $userId => [$label, $group]) {
            $this->findOrInsert('tenant_memberships', ['tenant_id' => $tenantId, 'user_id' => $userId], [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'status' => 'active',
                'membership_label' => $label,
                'is_active' => 1,
                'is_default' => $group === 'tenant_admin' ? 1 : 0,
            ] + $this->timestamps());

            $this->findOrInsert('tenant_iam_group_assignments', [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'group_name' => $group,
            ], [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'group_name' => $group,
            ] + $this->timestamps());
        }
    }

    private function seedAcademicStructure(int $tenantId, string $slug, array $profile): void
    {
        $sessionIds = [];
        foreach (['2025/2026' => 1, '2026/2027' => 0] as $session => $current) {
            $sessionIds[$session] = $this->findOrInsert('academic_sessions', ['tenant_id' => $tenantId, 'name' => $session], [
                'tenant_id' => $tenantId,
                'name' => $session,
                'start_date' => str_starts_with($session, '2025') ? '2025-09-01' : '2026-09-01',
                'end_date' => str_starts_with($session, '2025') ? '2026-08-31' : '2027-08-31',
                'is_current' => $current,
                'status' => 'active',
            ] + $this->timestamps());
        }

        $semesterIds = [];
        foreach (['first' => 'First Semester', 'second' => 'Second Semester'] as $code => $name) {
            $semesterIds[$code] = $this->findOrInsert('semesters', ['tenant_id' => $tenantId, 'name' => $name], [
                'tenant_id' => $tenantId,
                'name' => $name,
                'code' => $code,
                'is_active' => 1,
            ] + $this->timestamps());
        }

        $levelIds = [];
        foreach ([100, 200, 300] as $level) {
            $levelIds[$level] = $this->findOrInsert('levels', ['tenant_id' => $tenantId, 'name' => $level . ' Level'], [
                'tenant_id' => $tenantId,
                'name' => $level . ' Level',
                'code' => (string) $level,
                'sort_order' => $level,
                'is_active' => 1,
            ] + $this->timestamps());
        }

        $departments = $slug === 'demo-sht-lagos'
            ? [
                'CHT' => ['Community Health', '#16a34a'],
                'HIM' => ['Health Information Management', '#2563eb'],
                'EHT' => ['Environmental Health Technology', '#ca8a04'],
            ]
            : [
                'PHT' => ['Public Health Technology', '#7c3aed'],
                'NDT' => ['Nutrition and Dietetics', '#dc2626'],
            ];

        $departmentIds = [];
        foreach ($departments as $code => [$name, $color]) {
            $departmentIds[$code] = $this->findOrInsert('departments', ['tenant_id' => $tenantId, 'name' => $name], [
                'tenant_id' => $tenantId,
                'name' => $name,
                'code' => $code,
                'color_hex' => $color,
                'is_active' => 1,
            ] + $this->timestamps());

            $this->findOrInsert('department_identities', ['tenant_id' => $tenantId, 'department_id' => $departmentIds[$code]], [
                'tenant_id' => $tenantId,
                'department_id' => $departmentIds[$code],
                'color_hex' => $color,
                'icon_key' => strtolower($code),
            ] + $this->timestamps());
        }

        $programmes = $slug === 'demo-sht-lagos'
            ? [
                'CHEW' => ['Community Health Extension Worker', 'CHT', 3],
                'HIM-ND' => ['National Diploma in Health Information Management', 'HIM', 2],
                'EHT-ND' => ['National Diploma in Environmental Health Technology', 'EHT', 2],
            ]
            : [
                'PHT-DIP' => ['Diploma in Public Health Technology', 'PHT', 2],
                'NDT-ND' => ['National Diploma in Nutrition and Dietetics', 'NDT', 2],
            ];

        $programmeIds = [];
        foreach ($programmes as $code => [$name, $departmentCode, $duration]) {
            $programmeIds[$code] = $this->findOrInsert('programmes', ['tenant_id' => $tenantId, 'name' => $name], [
                'tenant_id' => $tenantId,
                'department_id' => $departmentIds[$departmentCode],
                'name' => $name,
                'code' => $code,
                'duration_years' => $duration,
                'is_active' => 1,
            ] + $this->timestamps());
        }

        $courseIds = [];
        foreach ($this->coursesFor($slug) as $courseCode => [$title, $departmentCode, $units]) {
            $courseIds[$courseCode] = $this->findOrInsert('courses', ['tenant_id' => $tenantId, 'course_code' => $courseCode], [
                'tenant_id' => $tenantId,
                'department_id' => $departmentIds[$departmentCode],
                'title' => $title,
                'course_code' => $courseCode,
                'credit_units' => $units,
                'is_active' => 1,
            ] + $this->timestamps());
        }

        foreach ($this->courseMappingsFor($slug) as [$programmeCode, $courseCode, $level, $semester]) {
            $this->findOrInsert('programme_courses', [
                'tenant_id' => $tenantId,
                'programme_id' => $programmeIds[$programmeCode],
                'course_id' => $courseIds[$courseCode],
                'level_id' => $levelIds[$level],
                'semester_id' => $semesterIds[$semester],
            ], [
                'tenant_id' => $tenantId,
                'programme_id' => $programmeIds[$programmeCode],
                'course_id' => $courseIds[$courseCode],
                'level_id' => $levelIds[$level],
                'semester_id' => $semesterIds[$semester],
                'is_required' => 1,
            ] + $this->timestamps());
        }
    }

    private function seedAuthorities(int $tenantId, string $slug): void
    {
        $authorities = [
            'school.configuration.manage' => 'Manage school configuration',
            'tenant.access.manage' => 'Manage tenant access control',
            'academic.configuration.manage' => 'Manage academic setup',
        ];

        $authorityIds = [];
        foreach ($authorities as $code => $name) {
            $authorityIds[$code] = $this->findOrInsert('operational_authorities', ['tenant_id' => $tenantId, 'code' => $code], [
                'tenant_id' => $tenantId,
                'code' => $code,
                'name' => $name,
                'description' => 'Demo Block 1 authority for ' . $name . '.',
                'is_active' => 1,
            ] + $this->timestamps());
        }

        $adminUserId = $slug === 'demo-sht-lagos' ? self::USERS['tenant_admin'] : self::USERS['second_tenant_admin'];
        foreach ($authorityIds as $authorityId) {
            $this->findOrInsert('membership_authorities', [
                'tenant_id' => $tenantId,
                'user_id' => $adminUserId,
                'authority_id' => $authorityId,
            ], [
                'tenant_id' => $tenantId,
                'user_id' => $adminUserId,
                'authority_id' => $authorityId,
                'scope_type' => null,
                'scope_id' => null,
            ] + $this->timestamps());
        }
    }

    private function seedAuditLogs(int $tenantId, string $slug): void
    {
        foreach ([
            'platform.tenant.create' => 'Demo tenant seeded.',
            'tenant.profile.create' => 'Demo tenant profile seeded.',
            'tenant.configuration.create' => 'Demo academic configuration seeded.',
            'tenant.operational_authority.grant' => 'Demo operational authorities seeded.',
            'tenant.theme.upsert' => 'Demo tenant theme seeded.',
        ] as $action => $summary) {
            $this->findOrInsert('audit_logs', [
                'tenant_id' => $tenantId,
                'action' => $action,
                'summary' => $summary,
            ], [
                'tenant_id' => $tenantId,
                'actor_user_id' => $slug === 'demo-sht-lagos' ? self::USERS['tenant_admin'] : self::USERS['second_tenant_admin'],
                'context' => str_starts_with($action, 'platform.') ? 'platform' : 'tenant',
                'action' => $action,
                'target_type' => 'seed_data',
                'target_id' => $slug,
                'summary' => $summary,
                'metadata' => json_encode(['seed' => static::class]),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Block1DemoSeeder',
                'created_at' => $this->now,
            ]);
        }
    }

    private function coursesFor(string $slug): array
    {
        if ($slug === 'demo-sht-lagos') {
            return [
                'CHT101' => ['Introduction to Community Health', 'CHT', 3],
                'CHT102' => ['Primary Health Care Practice', 'CHT', 3],
                'HIM101' => ['Health Records Management I', 'HIM', 2],
                'HIM102' => ['Medical Terminology', 'HIM', 2],
                'EHT101' => ['Environmental Sanitation I', 'EHT', 3],
                'EHT102' => ['Water and Waste Management', 'EHT', 3],
            ];
        }

        return [
            'PHT101' => ['Introduction to Public Health', 'PHT', 3],
            'PHT102' => ['Epidemiology Basics', 'PHT', 2],
            'NDT101' => ['Introduction to Nutrition', 'NDT', 3],
            'NDT102' => ['Food Science Basics', 'NDT', 2],
        ];
    }

    private function courseMappingsFor(string $slug): array
    {
        if ($slug === 'demo-sht-lagos') {
            return [
                ['CHEW', 'CHT101', 100, 'first'],
                ['CHEW', 'CHT102', 100, 'second'],
                ['HIM-ND', 'HIM101', 100, 'first'],
                ['HIM-ND', 'HIM102', 100, 'second'],
                ['EHT-ND', 'EHT101', 100, 'first'],
                ['EHT-ND', 'EHT102', 100, 'second'],
            ];
        }

        return [
            ['PHT-DIP', 'PHT101', 100, 'first'],
            ['PHT-DIP', 'PHT102', 100, 'second'],
            ['NDT-ND', 'NDT101', 100, 'first'],
            ['NDT-ND', 'NDT102', 100, 'second'],
        ];
    }

    private function findOrInsert(string $table, array $where, array $data): int
    {
        $existing = $this->db->table($table)->select('id')->where($where)->get()->getRowArray();
        if ($existing !== null) {
            return (int) $existing['id'];
        }

        $this->db->table($table)->insert($data);

        return (int) $this->db->insertID();
    }

    private function timestamps(): array
    {
        return [
            'created_by' => self::USERS['platform_admin'],
            'updated_by' => self::USERS['platform_admin'],
            'created_at' => $this->now,
            'updated_at' => $this->now,
            'deleted_at' => null,
        ];
    }
}

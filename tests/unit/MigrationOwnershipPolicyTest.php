<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

final class MigrationOwnershipPolicyTest extends CIUnitTestCase
{
    public function testMembershipLifecycleColumnsHaveOneMigrationOwner(): void
    {
        $files = glob(APPPATH . 'Database/Migrations/*.php') ?: [];
        $owners = [];
        foreach ($files as $file) {
            $source = file_get_contents($file);
            if (str_contains($source, "addColumn('tenant_memberships'")
                && str_contains($source, "'membership_label'")) {
                $owners[] = basename($file);
            }
        }

        $this->assertSame(['2026-05-28-140000_CreateTenantAccessControlTables.php'], $owners);
    }

    public function testMigrationsDoNotSilentlyAcceptPartialTables(): void
    {
        foreach (glob(APPPATH . 'Database/Migrations/*.php') ?: [] as $file) {
            $this->assertDoesNotMatchRegularExpression(
                '/->createTable\([^\n]+,\s*true\)/',
                file_get_contents($file),
                basename($file) . ' must fail on an unexpected partial table.'
            );
        }
    }

    public function testReadinessRollbackDoesNotDropAccessOwnedColumns(): void
    {
        $source = file_get_contents(APPPATH . 'Database/Migrations/2026-06-02-100000_CreateAdmissionReadinessTables.php');
        $this->assertStringNotContainsString("dropColumn('tenant_memberships'", $source);
    }

    public function testStabilizationSqlMatchesPhpReferentialActions(): void
    {
        foreach ([
            'phase_1_identity_tenant_boundary.sql' => [
                'REFERENCES tenant_memberships (id) ON DELETE RESTRICT ON UPDATE CASCADE',
                'REFERENCES tenants (id) ON DELETE RESTRICT ON UPDATE CASCADE',
            ],
            'phase_2_trusted_mutation_foundation.sql' => [
                'fk_idempotency_tenant',
                'REFERENCES tenants(id) ON DELETE RESTRICT ON UPDATE CASCADE',
            ],
            'phase_4_block_three_operations.sql' => [
                'fk_in_app_tenant',
                'REFERENCES tenants(id) ON DELETE RESTRICT ON UPDATE CASCADE',
            ],
        ] as $file => $fragments) {
            $source = file_get_contents(ROOTPATH . 'docs/stabilization/sql/' . $file);
            foreach ($fragments as $fragment) {
                $this->assertStringContainsString($fragment, $source, $file);
            }
        }
    }
}

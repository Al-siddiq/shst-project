<?php

namespace Tests\Database;

use App\Entities\TenantContext;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use RuntimeException;

final class Phase2IntegrityFoundationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    protected function tearDown(): void
    {
        service('tenantContextManager')->clear();
        parent::tearDown();
    }

    public function testTransactionalServiceRollsBackBusinessMutation(): void
    {
        $tenantId = $this->tenant('rollback-school');
        try {
            service('transactional')->run(function ($db) use ($tenantId): void {
                $db->table('academic_sessions')->insert(['tenant_id' => $tenantId, 'name' => 'Should Roll Back']);
                throw new RuntimeException('forced failure');
            });
            $this->fail('Expected forced transaction failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('forced failure', $exception->getMessage());
        }

        $this->assertSame(0, $this->db->table('academic_sessions')->where('tenant_id', $tenantId)->countAllResults());
    }

    public function testReferenceAllocationIsMonotonicWithinTenantNamespace(): void
    {
        $tenantId = $this->tenant('reference-school');
        service('tenantContextManager')->set(new TenantContext($tenantId, 'reference-school', 'route_slug'));

        $first = service('admissionReferenceGenerator')->next('application');
        $second = service('admissionReferenceGenerator')->next('application');

        $this->assertNotSame($first, $second);
        $this->assertStringEndsWith('000001', $first);
        $this->assertStringEndsWith('000002', $second);
    }

    public function testOutboxIdempotencyReturnsOriginalIntent(): void
    {
        $tenantId = $this->tenant('outbox-school');
        service('tenantContextManager')->set(new TenantContext($tenantId, 'outbox-school', 'route_slug'));

        $first = service('admissionNotificationDispatcher')->queue('test.event', 'user@example.test', ['id' => 1], 'email', 'test-key');
        $second = service('admissionNotificationDispatcher')->queue('test.event', 'user@example.test', ['id' => 1], 'email', 'test-key');

        $this->assertSame($first, $second);
        $this->assertSame(1, $this->db->table('admission_notification_outbox')->where('tenant_id', $tenantId)->countAllResults());
    }

    public function testCommandIdempotencyReplaysCommittedResponseAndRejectsChangedInput(): void
    {
        $tenantId = $this->tenant('command-school');
        service('tenantContextManager')->set(new TenantContext($tenantId, 'command-school', 'route_slug'));
        $calls = 0;
        $command = function () use (&$calls): array { $calls++; return ['resource_id' => 44]; };

        $first = service('idempotency')->execute('test.create', 'request-1', ['name' => 'Same'], $command);
        $second = service('idempotency')->execute('test.create', 'request-1', ['name' => 'Same'], $command);

        $this->assertSame($first, $second);
        $this->assertSame(1, $calls);
        $this->expectException(\InvalidArgumentException::class);
        service('idempotency')->execute('test.create', 'request-1', ['name' => 'Different'], $command);
    }

    public function testPhaseTwoOperationalColumnsExist(): void
    {
        foreach (['storage_state', 'scan_status', 'scan_attempts', 'scan_error', 'scanned_at'] as $column) {
            $this->assertTrue($this->db->fieldExists($column, 'application_documents'));
        }
        foreach (['idempotency_key', 'locked_at', 'locked_by', 'last_error', 'failed_at', 'sent_at'] as $column) {
            $this->assertTrue($this->db->fieldExists($column, 'admission_notification_outbox'));
        }
        $this->assertTrue($this->db->tableExists('idempotency_records'));
        $this->assertTrue($this->db->tableExists('tenant_cache_revisions'));
    }

    private function tenant(string $slug): int
    {
        $this->db->table('tenants')->insert(['school_name' => $slug, 'slug' => $slug, 'status' => 'active']);
        return (int) $this->db->insertID();
    }
}

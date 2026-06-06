<?php

namespace App\Services\Admissions;

use App\Models\AuditLogModel;
use App\Models\Tenant\Admissions\AdmissionNotificationOutboxModel;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;

/**
 * Phase 8 operational console for notification outbox and admissions audit logs.
 *
 * This service does not deliver messages; retry only re-queues existing tenant
 * notification intents so the actual delivery engine can be implemented later.
 */
class AdmissionOperationsService
{
    /** @return array<string, mixed> */
    public function overview(): array
    {
        $this->assertAuditAuthority();

        return [
            'outboxStatusCounts' => $this->outboxStatusCounts(),
            'recentOutbox' => (new AdmissionNotificationOutboxModel())->orderBy('updated_at', 'DESC')->findAll(50),
            'recentAuditLogs' => $this->recentAuditLogs(),
        ];
    }

    public function retryOutbox(int $outboxId): int
    {
        $this->assertAuditAuthority();
        $model = new AdmissionNotificationOutboxModel();
        $record = $model->find($outboxId);
        if ($record === null) {
            throw new InvalidArgumentException('Notification outbox record was not found for this tenant.');
        }
        if (($record['status'] ?? '') === 'sent') {
            throw new InvalidArgumentException('Sent notification outbox records cannot be retried.');
        }

        $model->update($outboxId, [
            'status' => 'pending',
            'available_at' => Time::now()->toDateTimeString(),
            'attempts' => ((int) ($record['attempts'] ?? 0)) + 1,
        ]);
        service('auditLogger')->record('admissions.outbox.retry_scheduled', [
            'target_type' => 'admission_notification_outbox',
            'target_id' => $outboxId,
            'summary' => 'Admissions notification outbox retry was scheduled.',
            'metadata' => ['previous_status' => $record['status'] ?? null],
        ]);

        return $outboxId;
    }

    /** @return array<string, int> */
    private function outboxStatusCounts(): array
    {
        $statuses = ['pending', 'processing', 'sent', 'failed'];
        $counts = [];
        foreach ($statuses as $status) {
            $counts[$status] = (new AdmissionNotificationOutboxModel())->where('status', $status)->countAllResults();
        }

        return $counts;
    }

    /** @return list<array<string, mixed>> */
    private function recentAuditLogs(): array
    {
        $context = service('tenantContextManager')->current();
        if (! $context->isResolved()) {
            return [];
        }

        // Audit logs are platform-wide, so Phase 8 applies an explicit tenant_id
        // condition before exposing admission operations history.
        return (new AuditLogModel())
            ->where('tenant_id', $context->tenantId)
            ->like('action', 'admissions.', 'after')
            ->orderBy('created_at', 'DESC')
            ->findAll(50);
    }

    private function assertAuditAuthority(): void
    {
        $context = service('tenantContextManager')->current();
        if (! service('tenantAccess')->isMember($context) || ! service('tenantAccess')->hasAuthority($context, 'admissions.audit.view')) {
            throw new InvalidArgumentException('You do not have authority to view admission operations.');
        }
    }
}

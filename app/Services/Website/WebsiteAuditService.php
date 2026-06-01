<?php

namespace App\Services\Website;

use App\Models\AuditLogModel;
use InvalidArgumentException;

/** Reads tenant-scoped website audit events without exposing unrelated platform history. */
class WebsiteAuditService
{
    /** @return array{items: list<array<string, mixed>>, page: int, totalPages: int, action: string} */
    public function listing(string $action = '', int $page = 1, int $perPage = 30): array
    {
        $this->authority();
        $context = service('tenantContextManager')->current();
        $page = max(1, $page);
        $total = $this->query($context->tenantId, $action)->countAllResults();
        $items = $this->query($context->tenantId, $action)->orderBy('created_at', 'DESC')->findAll($perPage, ($page - 1) * $perPage);

        return ['items' => $items, 'page' => $page, 'totalPages' => max(1, (int) ceil($total / $perPage)), 'action' => $action];
    }

    /** Builds a fresh model because CI count queries reset their builder state. */
    private function query(int $tenantId, string $action): AuditLogModel
    {
        $query = (new AuditLogModel())->where('tenant_id', $tenantId)->like('action', 'website.', 'after');
        if ($action !== '') {
            $query->like('action', $action);
        }

        return $query;
    }

    /** Dashboard callers are already protected by website.dashboard.view. @return list<array<string, mixed>> */
    public function recentForDashboard(int $limit = 8): array
    {
        $context = service('tenantContextManager')->current();

        return (new AuditLogModel())->where('tenant_id', $context->tenantId)->like('action', 'website.', 'after')->orderBy('created_at', 'DESC')->findAll($limit);
    }

    private function authority(): void
    {
        if (! service('tenantAccess')->hasAuthority(service('tenantContextManager')->current(), 'website.audit.view')) {
            throw new InvalidArgumentException('Required website audit authority is missing.');
        }
    }
}

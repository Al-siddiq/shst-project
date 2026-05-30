<?php

namespace App\Services;

use App\Libraries\Auth\IdentityGuard;
use App\Models\AuditLogModel;
use CodeIgniter\I18n\Time;

class AuditLogger
{
    public function record(string $action, array $details = []): void
    {
        $request = service('request');
        $context = service('tenantContextManager')->current();
        $actorId = (new IdentityGuard())->userId();

        (new AuditLogModel())->insert([
            'tenant_id' => $details['tenant_id'] ?? ($context->isResolved() ? $context->tenantId : null),
            'actor_user_id' => $details['actor_user_id'] ?? $actorId,
            'context' => $details['context'] ?? ($context->isResolved() ? 'tenant' : 'platform'),
            'action' => $action,
            'target_type' => $details['target_type'] ?? null,
            'target_id' => isset($details['target_id']) ? (string) $details['target_id'] : null,
            'summary' => $details['summary'] ?? null,
            'metadata' => isset($details['metadata']) ? json_encode($details['metadata']) : null,
            'ip_address' => method_exists($request, 'getIPAddress') ? $request->getIPAddress() : null,
            'user_agent' => substr((string) $request->getUserAgent(), 0, 255),
            'created_at' => Time::now()->toDateTimeString(),
        ]);
    }
}

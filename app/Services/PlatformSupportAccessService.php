<?php

namespace App\Services;

use App\Libraries\Auth\IdentityGuard;
use App\Models\PlatformSupportContextModel;
use App\Models\TenantModel;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;
use RuntimeException;

/** Short-lived, explicit, read-only platform support access; never impersonation. */
class PlatformSupportAccessService
{
    private const TTL_MINUTES = 30;

    public function __construct(private readonly IdentityGuard $guard = new IdentityGuard())
    {
    }

    /** @return array<string, mixed> */
    public function start(int $tenantId, string $reason): array
    {
        $this->assertPlatformAdministrator();
        $reason = trim($reason);
        if (mb_strlen($reason) < 10 || mb_strlen($reason) > 500) {
            throw new InvalidArgumentException('Support access requires a reason between 10 and 500 characters.');
        }
        $tenant = (new TenantModel())->find($tenantId);
        if ($tenant === null) {
            throw new InvalidArgumentException('Target tenant was not found.');
        }

        $this->endCurrent('superseded');
        $now = Time::now();
        $startedAt = $now->toDateTimeString();
        $expiresAt = $now->addMinutes(self::TTL_MINUTES)->toDateTimeString();
        $request = service('request');
        $token = bin2hex(random_bytes(24));
        $db = db_connect();
        $db->transStart();
        $id = (int) (new PlatformSupportContextModel())->insert([
            'public_token' => $token,
            'platform_user_id' => $this->guard->userId(),
            'tenant_id' => $tenantId,
            'reason' => $reason,
            'access_mode' => 'read_only',
            'status' => 'active',
            'started_at' => $startedAt,
            'expires_at' => $expiresAt,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => substr((string) $request->getUserAgent(), 0, 255),
        ], true);
        session()->set('platform_support_context_token', $token);
        service('auditLogger')->record('platform.support_context.started', [
            'tenant_id' => $tenantId,
            'context' => 'platform_support',
            'target_type' => 'platform_support_context',
            'target_id' => $id,
            'summary' => 'Platform administrator entered read-only tenant support context.',
            'metadata' => ['reason' => $reason, 'expires_at' => $expiresAt],
        ]);
        $db->transComplete();
        if (! $db->transStatus()) {
            session()->remove('platform_support_context_token');
            throw new RuntimeException('Support context could not be committed.');
        }

        return (new PlatformSupportContextModel())->find($id);
    }

    /** @return array<string, mixed>|null */
    public function current(): ?array
    {
        if (! $this->guard->isPlatformAdministrator()) {
            return null;
        }
        $token = session('platform_support_context_token');
        if (! is_string($token) || $token === '') {
            return null;
        }
        $record = (new PlatformSupportContextModel())
            ->where('public_token', $token)
            ->where('platform_user_id', $this->guard->userId())
            ->where('status', 'active')
            ->first();
        if ($record === null) {
            session()->remove('platform_support_context_token');

            return null;
        }
        if (strtotime((string) $record['expires_at']) <= time()) {
            $this->finish($record, 'expired');

            return null;
        }

        return $record;
    }

    public function endCurrent(string $status = 'ended'): void
    {
        $token = session('platform_support_context_token');
        if (! is_string($token) || $token === '') {
            return;
        }
        $record = (new PlatformSupportContextModel())->where('public_token', $token)->first();
        if ($record !== null && (int) $record['platform_user_id'] === $this->guard->userId() && $record['status'] === 'active') {
            $this->finish($record, $status);
        }
    }

    private function finish(array $record, string $status): void
    {
        $db = db_connect();
        $db->transStart();
        (new PlatformSupportContextModel())->update((int) $record['id'], [
            'status' => $status,
            'ended_at' => Time::now()->toDateTimeString(),
        ]);
        service('auditLogger')->record('platform.support_context.' . $status, [
            'tenant_id' => $record['tenant_id'],
            'context' => 'platform_support',
            'target_type' => 'platform_support_context',
            'target_id' => $record['id'],
            'summary' => 'Platform support context closed.',
        ]);
        $db->transComplete();
        if (! $db->transStatus()) {
            throw new RuntimeException('Support context closure could not be committed.');
        }
        session()->remove('platform_support_context_token');
    }

    private function assertPlatformAdministrator(): void
    {
        if (! $this->guard->isPlatformAdministrator() || service('tenantIdentity')->membershipForUser($this->guard->userId()) !== null) {
            throw new InvalidArgumentException('A separate platform administrator identity is required.');
        }
    }
}

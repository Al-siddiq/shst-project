<?php

namespace App\Libraries\Auth;

use Config\Block1;

/** Keeps Shield authentication behind one narrow application adapter. */
class IdentityGuard
{
    /** @var list<string> */
    private const TENANT_GROUPS = ['tenant_super_admin', 'tenant_admin', 'lecturer', 'student', 'applicant'];

    public function check(): bool
    {
        if (function_exists('auth') && auth()->loggedIn()) {
            return true;
        }

        return $this->testingUserId() !== null;
    }

    public function userId(): ?int
    {
        if (function_exists('auth')) {
            $id = auth()->id();
            if ($id !== null) {
                return (int) $id;
            }
        }

        return $this->testingUserId();
    }

    public function identifierAllowed(string $identifier): bool
    {
        return $this->identifierType($identifier) !== null;
    }

    public function identifierType(string $identifier): ?string
    {
        $identifier = trim($identifier);
        if (in_array('email', config(Block1::class)->loginIdentifiers, true)
            && filter_var(strtolower($identifier), FILTER_VALIDATE_EMAIL) !== false) {
            return 'email';
        }

        if (in_array('phone', config(Block1::class)->loginIdentifiers, true)) {
            try {
                service('applicantIdentityNormalizer')->normalize($identifier);

                return 'phone';
            } catch (\InvalidArgumentException) {
                // Invalid identifiers must fail closed without leaking account state.
            }
        }

        return null;
    }

    /** @return list<string> */
    public function groups(): array
    {
        if (function_exists('auth')) {
            $user = auth()->user();
            if ($user !== null && method_exists($user, 'getGroups')) {
                return array_values(array_unique(array_map('strval', $user->getGroups())));
            }
        }

        if (ENVIRONMENT === 'testing') {
            $groups = session('auth_groups') ?? [];

            return is_array($groups) ? array_values(array_map('strval', $groups)) : [];
        }

        return [];
    }

    public function inGroup(string ...$groups): bool
    {
        return array_intersect($groups, $this->groups()) !== [];
    }

    public function isPlatformAdministrator(): bool
    {
        return $this->inGroup('platform_admin');
    }

    public function isTenantIdentity(): bool
    {
        return array_intersect(self::TENANT_GROUPS, $this->groups()) !== []
            && ! $this->isPlatformAdministrator();
    }

    /**
     * Isolated repository tests may use a session identity without fabricating
     * Shield internals. Production and development must authenticate via Shield.
     */
    private function testingUserId(): ?int
    {
        if (ENVIRONMENT !== 'testing') {
            return null;
        }

        $id = session('user_id');

        return $id !== null ? (int) $id : null;
    }
}

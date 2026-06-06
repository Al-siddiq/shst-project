<?php

namespace App\Libraries\Auth;

use Config\Block1;

/** Keeps Shield authentication behind one narrow application adapter. */
class IdentityGuard
{
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
        $config = config(Block1::class);

        return in_array($identifier, $config->loginIdentifiers, true);
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

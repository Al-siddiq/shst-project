<?php

namespace App\Libraries\Auth;

use Config\Block1;

class IdentityGuard
{
    public function check(): bool
    {
        if (function_exists('auth')) {
            $auth = auth();
            if (method_exists($auth, 'loggedIn')) {
                return (bool) $auth->loggedIn();
            }
        }

        return session()->has('user_id');
    }

    public function identifierAllowed(string $identifier): bool
    {
        $config = config(Block1::class);
        return in_array($identifier, $config->loginIdentifiers, true);
    }
}

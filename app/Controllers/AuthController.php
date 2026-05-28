<?php

namespace App\Controllers;

use App\Libraries\Auth\IdentityGuard;
use App\Traits\ApiResponseTrait;

class AuthController extends BaseController
{
    use ApiResponseTrait;

    public function login()
    {
        return $this->ok('Authentication baseline is active. Integrate CodeIgniter Shield login UI in deployment environment.', [
            'accepted_identifiers' => config('Block1')->loginIdentifiers,
            'shield_integration' => function_exists('auth'),
        ]);
    }

    public function identifierPolicy(string $identifier)
    {
        $guard = new IdentityGuard();

        return $this->ok('Identifier policy evaluated.', [
            'identifier' => $identifier,
            'allowed' => $guard->identifierAllowed($identifier),
        ]);
    }
}

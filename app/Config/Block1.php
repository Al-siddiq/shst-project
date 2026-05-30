<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Block1 extends BaseConfig
{
    /**
     * Baseline identifier strategy for Block 1 authentication.
     *
     * Shield will remain the identity foundation. We explicitly declare
     * both email and phone identifiers as accepted login credentials
     * for tenant users and platform administrators.
     */
    public array $loginIdentifiers = ['email', 'phone'];

    /**
     * Session timeout for protected internal portals (minutes).
     */
    public int $sessionTimeoutMinutes = 120;

    /**
     * When true, protected endpoints return a standard JSON error payload
     * for API and AJAX clients instead of redirects.
     */
    public bool $preferJsonErrors = true;
}

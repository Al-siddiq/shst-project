<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Shield\Config\Auth as ShieldAuth;

/**
 * Application-owned Shield configuration extension point.
 *
 * Keep global authentication inside Shield. Tenant membership, applicant
 * ownership, and operational authority remain separate application concerns.
 */
class Auth extends ShieldAuth
{
}

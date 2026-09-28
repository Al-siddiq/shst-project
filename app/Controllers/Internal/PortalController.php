<?php

namespace App\Controllers\Internal;

use App\Controllers\BaseController;
use App\Libraries\Auth\IdentityGuard;

class PortalController extends BaseController
{
    public function dashboard()
    {
        if ((new IdentityGuard())->isPlatformAdministrator()) {
            return redirect()->to(site_url('platform/tenants'));
        }

        return redirect()->to(site_url('tenant/admin'));
    }
}

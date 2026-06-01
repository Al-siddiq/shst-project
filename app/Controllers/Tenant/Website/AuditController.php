<?php

namespace App\Controllers\Tenant\Website;

use App\Controllers\BaseController;

/** Renders website-only audit history for authorized tenant administrators. */
class AuditController extends BaseController
{
    public function index(): string
    {
        return view('tenant/website/audit/index', service('websiteAudit')->listing(
            (string) $this->request->getGet('action'),
            (int) ($this->request->getGet('page') ?: 1),
        ));
    }
}

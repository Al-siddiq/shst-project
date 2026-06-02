<?php

namespace App\Controllers\Tenant\Admissions;

use App\Controllers\BaseController;

/** Renders tenant-scoped Phase 1 admission setup gaps and counts. */
class DashboardController extends BaseController
{
    public function index(): string
    {
        return view('tenant/admissions/dashboard', service('admissionDashboard')->summary());
    }
}

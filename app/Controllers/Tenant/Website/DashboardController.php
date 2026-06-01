<?php

namespace App\Controllers\Tenant\Website;

use App\Controllers\BaseController;

/**
 * Renders tenant website readiness and maintenance metrics.
 *
 * The controller intentionally delegates all counting to the service so future
 * JSON dashboard islands cannot drift from the server-rendered baseline.
 */
class DashboardController extends BaseController
{
    public function index(): string
    {
        return view('tenant/website/dashboard', service('websiteDashboard')->summary());
    }
}

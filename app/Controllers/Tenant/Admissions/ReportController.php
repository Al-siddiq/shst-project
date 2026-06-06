<?php

namespace App\Controllers\Tenant\Admissions;

use App\Controllers\BaseController;
use InvalidArgumentException;

/** Phase 8 tenant-staff reports and safe CSV exports. */
class ReportController extends BaseController
{
    protected $helpers = ['form'];

    public function index(): string
    {
        return view('tenant/admissions/reports/index', service('admissionReport')->dashboard());
    }

    public function export(string $report)
    {
        try {
            $export = service('admissionReport')->csvExport($report);
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->with('errors', ['admissions' => $exception->getMessage()]);
        }

        return $this->response
            ->setHeader('Content-Type', $export['mime'])
            ->setHeader('Content-Disposition', 'attachment; filename="' . $export['filename'] . '"')
            ->setBody($export['content']);
    }
}

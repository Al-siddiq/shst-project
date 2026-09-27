<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/** Minimal orchestrator probes; never exposes credentials or exception details. */
final class HealthController extends BaseController
{
    public function live(): ResponseInterface
    {
        return $this->response->setJSON(['status' => 'ok']);
    }

    public function ready(): ResponseInterface
    {
        $checks = ['database' => false, 'writable' => false];
        try {
            $checks['database'] = db_connect()->query('SELECT 1 AS ready')->getRow() !== null;
        } catch (Throwable) {
            // The probe deliberately returns only a component state.
        }

        $checks['writable'] = is_dir(WRITEPATH) && is_writable(WRITEPATH);
        $ready = ! in_array(false, $checks, true);

        return $this->response
            ->setStatusCode($ready ? 200 : 503)
            ->setJSON(['status' => $ready ? 'ready' : 'unavailable', 'checks' => $checks]);
    }
}

<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

/** Read-only production preflight. It makes no schema or application changes. */
final class ReleasePreflight extends BaseCommand
{
    protected $group = 'Operations';
    protected $name = 'release:preflight';
    protected $description = 'Check required runtime, database, schema, and writable paths before release.';

    public function run(array $params)
    {
        $results = [];
        $results['php'] = version_compare(PHP_VERSION, '8.2.0', '>=');
        foreach (['intl', 'mbstring', 'json', 'openssl', 'pdo', 'mysqli'] as $extension) {
            $results['ext:' . $extension] = extension_loaded($extension);
        }
        foreach ([WRITEPATH, WRITEPATH . 'cache', WRITEPATH . 'logs', WRITEPATH . 'uploads'] as $path) {
            $results['writable:' . basename($path)] = is_dir($path) && is_writable($path);
        }

        try {
            $db = db_connect();
            $results['database'] = $db->query('SELECT 1')->getRow() !== null;
            if ($db->DBDriver === 'MySQLi') {
                $versionRow = $db->query('SELECT VERSION() AS version')->getRow();
                $version = (string) ($versionRow->version ?? '');
                $results['mysql:8.4'] = str_starts_with($version, '8.4.');
                $isolationRow = $db->query('SELECT @@transaction_isolation AS level')->getRow();
                $isolation = strtoupper(str_replace('-', ' ', (string) ($isolationRow->level ?? '')));
                $results['mysql:read-committed'] = $isolation === 'READ COMMITTED';
            } else {
                $results['mysql:8.4'] = false;
                $results['mysql:read-committed'] = false;
            }
            foreach (['tenants', 'tenant_memberships', 'audit_logs', 'applicant_applications', 'admission_notification_outbox', 'in_app_notifications'] as $table) {
                $results['table:' . $table] = $db->tableExists($table);
            }
        } catch (Throwable) {
            $results['database'] = false;
        }

        foreach ($results as $check => $passed) {
            CLI::write(($passed ? '[PASS] ' : '[FAIL] ') . $check, $passed ? 'green' : 'red');
        }
        if (in_array(false, $results, true)) {
            CLI::error('Release preflight failed. Do not deploy or start Block 4.');
            return EXIT_ERROR;
        }
        CLI::write('Release preflight passed.', 'green');
        return EXIT_SUCCESS;
    }
}

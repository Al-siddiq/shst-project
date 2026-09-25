<?php
namespace App\Services\Files;

use Config\Admissions;

final class ClamAvScanner implements MalwareScannerInterface
{
    public function __construct(private readonly ?Admissions $config = null) {}

    public function scan(string $absolutePath): array
    {
        if (! is_file($absolutePath)) return ['status' => 'unavailable', 'detail' => 'Quarantined object is missing.'];
        $config = $this->config ?? config(Admissions::class);
        $command = 'timeout ' . max(5, $config->malwareScannerTimeoutSeconds) . 's ' . escapeshellcmd($config->malwareScannerBinary) . ' --no-summary -- ' . escapeshellarg($absolutePath);
        $pipes = [];
        $process = @proc_open($command, [['pipe','r'],['pipe','w'],['pipe','w']], $pipes);
        if (! is_resource($process)) return ['status' => 'unavailable', 'detail' => 'Malware scanner could not be started.'];
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]); $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        $exit = proc_close($process);
        if ($exit === 0) return ['status' => 'clean', 'detail' => null];
        if ($exit === 1) return ['status' => 'infected', 'detail' => trim($output ?: $error) ?: 'Malware detected.'];
        return ['status' => 'unavailable', 'detail' => trim($error ?: $output) ?: 'Malware scanner unavailable.'];
    }
}

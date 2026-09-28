<?php

namespace App\Services;

use RuntimeException;
use Throwable;

/** Executes a business mutation inside the caller's database transaction. */
class TransactionalService
{
    public function run(callable $operation): mixed
    {
        $db = db_connect();
        $db->transException(true)->transStart();

        try {
            $result = $operation($db);
            $db->transComplete();
        } catch (Throwable $exception) {
            $db->transRollback();
            throw $exception;
        }

        if (! $db->transStatus()) {
            throw new RuntimeException('The business operation could not be committed.');
        }

        return $result;
    }
}

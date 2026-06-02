<?php

namespace App\Services\Admissions;

use Config\Admissions;
use InvalidArgumentException;
use RuntimeException;

/**
 * Resolves private applicant-document files below WRITEPATH only.
 *
 * Upload persistence arrives in Phase 3. Phase 0 establishes the storage and
 * authorized-read boundary first so no future controller can expose raw paths.
 */
class ApplicantDocumentStorage
{
    public function storageRoot(): string
    {
        return rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . trim(config(Admissions::class)->documentStorageDirectory, '/');
    }

    /** @param array<string, mixed> $document */
    public function privateFile(array $document): string
    {
        $relativePath = (string) ($document['storage_path'] ?? '');
        if ($relativePath === '' || str_contains($relativePath, '..') || str_starts_with($relativePath, '/')) {
            throw new RuntimeException('Unsafe applicant document storage path detected.');
        }

        $path = $this->storageRoot() . DIRECTORY_SEPARATOR . $relativePath;
        if (! is_file($path)) {
            throw new InvalidArgumentException('Applicant document is unavailable.');
        }

        return $path;
    }
}

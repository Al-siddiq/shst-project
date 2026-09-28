<?php

namespace App\Services\Admissions;

use CodeIgniter\HTTP\Files\UploadedFile;
use Config\Admissions;
use InvalidArgumentException;
use RuntimeException;
use App\Services\Files\PrivateDocumentStorageInterface;

/**
 * Keeps applicant documents private below WRITEPATH and validates every write.
 */
class ApplicantDocumentStorage implements PrivateDocumentStorageInterface
{
    public function storageRoot(): string
    {
        return rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . trim(config(Admissions::class)->documentStorageDirectory, '/');
    }

    /** @param array<string, mixed> $application @return array<string, mixed> */
    public function store(UploadedFile $file, array $application, string $documentType, ?array $requirement = null): array
    {
        if (! $file->isValid()) {
            throw new InvalidArgumentException('Upload failed. Please retry with a valid file.');
        }

        $config = config(Admissions::class);
        $allowedMimeTypes = $requirement !== null && ! empty($requirement['allowed_mime_types'])
            ? array_map('trim', explode(',', (string) $requirement['allowed_mime_types']))
            : array_keys($config->documentExtensionsByMimeType);
        $maximumSize = $requirement !== null && ! empty($requirement['maximum_size_bytes']) ? (int) $requirement['maximum_size_bytes'] : $config->maximumDocumentSizeBytes;
        $mimeType = (string) ($file->getMimeType() ?: $file->getClientMimeType());
        if (! in_array($mimeType, $allowedMimeTypes, true) || ! isset($config->documentExtensionsByMimeType[$mimeType])) {
            throw new InvalidArgumentException('Unsupported document type. Upload PDF, JPG, or PNG only.');
        }
        if ($file->getSize() > $maximumSize) {
            throw new InvalidArgumentException('Document is larger than the configured admission upload limit.');
        }

        $extension = $config->documentExtensionsByMimeType[$mimeType];
        // New uploads enter a private quarantine namespace. Phase 4's scanner
        // promotes only clean objects; callers must never serve this path.
        $directory = 'quarantine' . DIRECTORY_SEPARATOR . 'tenant-' . (int) $application['tenant_id'] . DIRECTORY_SEPARATOR . 'application-' . (int) $application['id'];
        $absoluteDirectory = $this->storageRoot() . DIRECTORY_SEPARATOR . $directory;
        if (! is_dir($absoluteDirectory) && ! mkdir($absoluteDirectory, 0775, true) && ! is_dir($absoluteDirectory)) {
            throw new RuntimeException('Private admission upload directory could not be created.');
        }

        $safeName = preg_replace('/[^a-z0-9_-]+/i', '-', strtolower($documentType)) . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
        $file->move($absoluteDirectory, $safeName, true);
        $relativePath = $directory . DIRECTORY_SEPARATOR . $safeName;
        $absolutePath = $this->storageRoot() . DIRECTORY_SEPARATOR . $relativePath;

        return [
            'storage_path' => str_replace(DIRECTORY_SEPARATOR, '/', $relativePath),
            'original_name' => $file->getClientName(),
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size_bytes' => $file->getSize(),
            'checksum_sha256' => hash_file('sha256', $absolutePath),
        ];
    }

    /** Best-effort compensation when metadata cannot be committed. */
    public function discard(array $stored): void
    {
        try {
            $path = $this->privateFile($stored);
            if (! unlink($path)) {
                throw new RuntimeException('Quarantined applicant document could not be removed.');
            }
        } catch (InvalidArgumentException) {
            // An already absent staged object is a successful compensation.
        }
    }

    /** @return array{storage_path:string,previous_path:string} */
    public function promoteClean(array $document): array
    {
        $source = $this->privateFile($document);
        $relative = (string) $document['storage_path'];
        if (! str_starts_with($relative, 'quarantine/')) throw new RuntimeException('Only quarantined documents can be promoted.');
        $targetRelative = 'private/' . substr($relative, strlen('quarantine/'));
        $target = $this->storageRoot() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $targetRelative);
        if (! is_dir(dirname($target)) && ! mkdir(dirname($target), 0770, true) && ! is_dir(dirname($target))) throw new RuntimeException('Private document directory could not be created.');
        if (! rename($source, $target)) throw new RuntimeException('Clean document could not be promoted from quarantine.');
        return ['storage_path' => $targetRelative, 'previous_path' => $relative];
    }

    public function rollbackPromotion(array $promotion): void
    {
        $active = $this->storageRoot() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $promotion['storage_path']);
        $quarantine = $this->storageRoot() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $promotion['previous_path']);
        if (is_file($active)) {
            if (! is_dir(dirname($quarantine))) mkdir(dirname($quarantine), 0770, true);
            @rename($active, $quarantine);
        }
    }

    /** @param array<string, mixed> $document */
    public function privateFile(array $document): string
    {
        $relativePath = (string) ($document['storage_path'] ?? '');
        if ($relativePath === '' || str_contains($relativePath, '..') || str_starts_with($relativePath, '/')) {
            throw new RuntimeException('Unsafe applicant document storage path detected.');
        }

        $path = $this->storageRoot() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        if (! is_file($path)) {
            throw new InvalidArgumentException('Applicant document is unavailable.');
        }

        return $path;
    }
}

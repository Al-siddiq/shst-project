<?php

namespace App\Services\Admissions;

use CodeIgniter\HTTP\Files\UploadedFile;
use Config\Admissions;
use InvalidArgumentException;
use RuntimeException;

/**
 * Keeps applicant documents private below WRITEPATH and validates every write.
 */
class ApplicantDocumentStorage
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
        $directory = 'tenant-' . (int) $application['tenant_id'] . DIRECTORY_SEPARATOR . 'application-' . (int) $application['id'];
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

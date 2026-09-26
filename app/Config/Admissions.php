<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Neutral platform admission defaults for Phase 0.
 *
 * School-specific requirements belong in tenant-owned records introduced by
 * later phases. This class contains security and storage policy only.
 */
class Admissions extends BaseConfig
{
    /** @var array<string, string> */
    public array $documentExtensionsByMimeType = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    public int $maximumDocumentSizeBytes = 5_242_880;

    public string $documentStorageDirectory = 'uploads/admissions';

    /** Replaceable malware scanner command; production may point to clamdscan. */
    public string $malwareScannerBinary = 'clamdscan';
    public int $malwareScannerTimeoutSeconds = 60;
    public int $notificationMaxAttempts = 5;
    public int $notificationRetryBaseSeconds = 60;

    /** Prefix is neutral; tenants may configure display patterns in Phase 1. */
    public string $applicationReferencePrefix = 'APP';

    public int $applicationReferencePadding = 6;

    /** @var list<string> */
    public array $acceptedLoginIdentifierTypes = ['email', 'phone'];
}

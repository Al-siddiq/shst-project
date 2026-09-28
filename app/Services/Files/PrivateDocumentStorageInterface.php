<?php
namespace App\Services\Files;
use CodeIgniter\HTTP\Files\UploadedFile;
interface PrivateDocumentStorageInterface
{
    public function store(UploadedFile $file,array $application,string $documentType,?array $requirement=null): array;
    public function privateFile(array $document): string;
    public function discard(array $stored): void;
    public function promoteClean(array $document): array;
    public function rollbackPromotion(array $promotion): void;
}

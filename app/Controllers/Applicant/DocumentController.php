<?php

namespace App\Controllers\Applicant;

use App\Controllers\BaseController;
use App\Models\Tenant\Admissions\ApplicationDocumentModel;
use App\Traits\ApiResponseTrait;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\DownloadResponse;
use InvalidArgumentException;

/** Streams and uploads private applicant files after owner/reviewer checks. */
class DocumentController extends BaseController
{
    use ApiResponseTrait;

    public function upload(string $applicationToken)
    {
        $file = $this->request->getFile('document');
        $documentType = (string) $this->request->getPost('document_type');
        if ($file === null || $documentType === '') {
            return $this->fail('Validation failed.', ['document' => 'Choose a required document and file.'], 422);
        }

        try {
            $document = service('applicantDocument')->upload($applicationToken, $documentType, $file);
        } catch (InvalidArgumentException $exception) {
            return $this->fail('Validation failed.', ['document' => $exception->getMessage()], 422);
        }

        return $this->request->isAJAX()
            ? $this->ok('Document uploaded.', ['document' => $document])
            : redirect()->back()->with('message', 'Document uploaded.');
    }

    public function download(string $token): DownloadResponse
    {
        $context = service('tenantContextManager')->current();
        $document = service('applicantAccessPolicy')->ownedDocument($context, $token);

        if ($document === null && service('tenantAccess')->isMember($context) && service('tenantAccess')->hasAuthority($context, 'admissions.documents.review')) {
            // TenantScopedModel still constrains this reviewer lookup to the
            // active tenant, even after the staff authority has been proven.
            $document = (new ApplicationDocumentModel())->where('public_token', $token)->first();
        }

        if ($document === null) {
            throw PageNotFoundException::forPageNotFound('Applicant document was not found.');
        }

        $path = service('applicantDocumentStorage')->privateFile($document);

        return $this->response->download($path, null)->setFileName((string) $document['original_name']);
    }
}

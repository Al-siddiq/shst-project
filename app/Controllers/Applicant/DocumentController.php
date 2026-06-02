<?php

namespace App\Controllers\Applicant;

use App\Controllers\BaseController;
use App\Models\Tenant\Admissions\ApplicationDocumentModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\DownloadResponse;

/** Streams private applicant files only after owner or reviewer authorization. */
class DocumentController extends BaseController
{
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

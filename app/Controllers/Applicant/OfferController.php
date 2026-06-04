<?php

namespace App\Controllers\Applicant;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

/** Applicant-facing Phase 5 offer page; acceptance/decline waits for Phase 7. */
class OfferController extends BaseController
{
    public function index(): string
    {
        $offer = service('admissionDecision')->currentOfferForApplicant();
        if ($offer === null) {
            throw PageNotFoundException::forPageNotFound('No active admission offer was found for this applicant.');
        }

        return view('applicant/offer', array_merge(service('publicWebsite')->page('apply', 'Admission offer'), ['offer' => $offer, 'snapshot' => json_decode((string) $offer['offer_snapshot_json'], true) ?: []]));
    }
}

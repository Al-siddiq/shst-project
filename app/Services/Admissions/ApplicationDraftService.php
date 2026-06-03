<?php

namespace App\Services\Admissions;

use App\Models\Tenant\Admissions\AdmissionProgrammeOpeningModel;
use App\Models\Tenant\Admissions\ApplicantApplicationModel;
use App\Models\Tenant\Admissions\ApplicationBiodataDraftModel;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;
use RuntimeException;

/** Owns applicant draft creation, resume, and biodata autosave. */
class ApplicationDraftService
{
    /** @return list<array<string, mixed>> */
    public function draftsForCurrentApplicant(): array
    {
        $profile = $this->profile();

        return (new ApplicantApplicationModel())->where('applicant_profile_id', $profile['id'])->orderBy('updated_at', 'DESC')->findAll();
    }

    /** @return array<string, mixed> */
    public function start(int $programmeOpeningId): array
    {
        $profile = $this->profile();
        $opening = $this->assertOpenProgramme($programmeOpeningId);
        $model = new ApplicantApplicationModel();
        $existing = $model->where('applicant_profile_id', $profile['id'])->where('admission_cycle_id', $opening['admission_cycle_id'])->where('programme_opening_id', $programmeOpeningId)->first();
        if ($existing !== null) {
            service('auditLogger')->record('admissions.application.resumed', ['target_type' => 'applicant_application', 'target_id' => $existing['id'], 'summary' => 'Applicant resumed a draft application.']);

            return $this->withBiodata($existing);
        }

        $now = Time::now()->toDateTimeString();
        $id = (int) $model->insert([
            'applicant_profile_id' => $profile['id'],
            'admission_cycle_id' => $opening['admission_cycle_id'],
            'programme_opening_id' => $programmeOpeningId,
            'public_token' => bin2hex(random_bytes(16)),
            'status' => 'draft',
            'biodata_status' => 'not_started',
            'started_at' => $now,
            'last_saved_at' => $now,
        ], true);
        service('auditLogger')->record('admissions.application.started', ['target_type' => 'applicant_application', 'target_id' => $id, 'summary' => 'Applicant started a draft application.']);

        return $this->withBiodata($model->find($id));
    }

    /** @return array<string, mixed> */
    public function resume(string $token): array
    {
        $application = service('applicantAccessPolicy')->ownedApplication(service('tenantContextManager')->current(), $token);
        if ($application === null) {
            throw new InvalidArgumentException('Draft application was not found for this applicant.');
        }
        service('auditLogger')->record('admissions.application.resumed', ['target_type' => 'applicant_application', 'target_id' => $application['id'], 'summary' => 'Applicant resumed a draft application.']);

        return $this->withBiodata($application);
    }

    /** @param array<string, mixed> $payload @return array<string, mixed> */
    public function saveBiodata(string $token, array $payload): array
    {
        $application = service('applicantAccessPolicy')->ownedApplication(service('tenantContextManager')->current(), $token);
        if ($application === null) {
            throw new InvalidArgumentException('Draft application was not found for this applicant.');
        }
        service('admissionCorrectionWindow')->assertApplicantMayEdit($application);
        foreach (['phone_e164', 'next_of_kin_phone_e164', 'guardian_phone_e164'] as $field) {
            if (! empty($payload[$field])) {
                $payload[$field] = service('applicantIdentityNormalizer')->normalizeNigerianPhone((string) $payload[$field]);
            }
        }
        if (! empty($payload['email'])) {
            $payload['email'] = service('applicantIdentityNormalizer')->normalize((string) $payload['email'])['value'];
        }
        $payload['completion_percent'] = $this->completion($payload);
        $payload['last_saved_at'] = Time::now()->toDateTimeString();
        $payload['applicant_application_id'] = $application['id'];

        $model = new ApplicationBiodataDraftModel();
        $existing = $model->where('applicant_application_id', $application['id'])->first();
        $id = $existing === null ? (int) $model->insert($payload, true) : (int) $existing['id'];
        if ($existing !== null) {
            $model->update($id, $payload);
        }
        (new ApplicantApplicationModel())->update((int) $application['id'], ['biodata_status' => $payload['completion_percent'] >= 50 ? 'in_progress' : 'started', 'last_saved_at' => $payload['last_saved_at']]);
        service('auditLogger')->record('admissions.applicant.biodata_saved', ['target_type' => 'applicant_application', 'target_id' => $application['id'], 'summary' => 'Applicant biodata draft saved.', 'metadata' => ['completion_percent' => $payload['completion_percent']]]);

        return $this->withBiodata((new ApplicantApplicationModel())->find((int) $application['id']));
    }

    /** @return array<string, mixed> */
    private function profile(): array
    {
        $profile = service('applicantAccessPolicy')->currentProfile(service('tenantContextManager')->current());
        if ($profile === null) {
            throw new InvalidArgumentException('Create an applicant profile before starting an application.');
        }

        return $profile;
    }

    /** @return array<string, mixed> */
    private function assertOpenProgramme(int $programmeOpeningId): array
    {
        foreach (service('publicAdmissions')->openProgrammes() as $opening) {
            if ((int) $opening['id'] === $programmeOpeningId) {
                $row = (new AdmissionProgrammeOpeningModel())->find($programmeOpeningId);
                if ($row === null) {
                    break;
                }

                return $row;
            }
        }

        throw new InvalidArgumentException('Select an open admission programme for the active tenant.');
    }

    /** @param array<string, mixed> $application @return array<string, mixed> */
    private function withBiodata(?array $application): array
    {
        if ($application === null) {
            throw new RuntimeException('Applicant draft could not be loaded.');
        }
        $application['biodata'] = (new ApplicationBiodataDraftModel())->where('applicant_application_id', $application['id'])->first();

        return $application;
    }

    /** @param array<string, mixed> $payload */
    private function completion(array $payload): int
    {
        $fields = ['surname', 'first_name', 'gender', 'date_of_birth', 'phone_e164', 'email', 'residential_address', 'state_of_origin', 'lga_of_origin', 'nationality'];
        $filled = 0;
        foreach ($fields as $field) {
            if (! empty($payload[$field])) {
                $filled++;
            }
        }

        return (int) floor(($filled / count($fields)) * 100);
    }
}

<?php

namespace App\Services\Admissions;

use App\Models\Tenant\Admissions\AdmissionListEntryModel;
use App\Models\Tenant\Admissions\AdmissionListPublicationModel;
use App\Models\Tenant\Admissions\AdmissionOfferModel;
use App\Models\Tenant\Admissions\ApplicantApplicationModel;
use App\Models\Tenant\Admissions\ApplicationBiodataDraftModel;
use App\Models\Tenant\ProgrammeModel;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;

/** Owns Phase 6 private preview and public admission-list publication. */
class AdmissionListPublicationService
{
    private const SAFE_PUBLIC_FIELDS = ['application_number', 'applicant_display_name', 'programme_name'];
    private const ACTIVE_OFFER_STATUSES = ['issued', 'pending_acceptance'];

    /** @return array<string, mixed> */
    public function workspace(): array
    {
        $this->assertAuthority('admissions.lists.preview');

        return [
            'publications' => (new AdmissionListPublicationModel())->orderBy('created_at', 'DESC')->findAll(50),
            'published' => (new AdmissionListPublicationModel())->where('status', 'published')->orderBy('published_at', 'DESC')->findAll(50),
            'metrics' => $this->metrics(),
        ];
    }

    public function create(array $payload): int
    {
        $this->assertAuthority('admissions.lists.preview');
        $cycleId = (int) ($payload['admission_cycle_id'] ?? 0);
        $title = trim((string) ($payload['title'] ?? ''));
        if ($cycleId < 1 || $title === '') {
            throw new InvalidArgumentException('Admission cycle and title are required for a publication draft.');
        }

        $model = new AdmissionListPublicationModel();
        $version = (int) ($payload['version_number'] ?? 0);
        if ($version < 1) {
            $last = $model->where('admission_cycle_id', $cycleId)->orderBy('version_number', 'DESC')->first();
            $version = $last === null ? 1 : ((int) $last['version_number'] + 1);
        }
        $id = (int) $model->insert([
            'admission_cycle_id' => $cycleId,
            'programme_opening_id' => empty($payload['programme_opening_id']) ? null : (int) $payload['programme_opening_id'],
            'title' => $title,
            'version_number' => $version,
            'public_token' => bin2hex(random_bytes(12)),
            'status' => 'draft',
            'safe_fields_json' => json_encode(self::SAFE_PUBLIC_FIELDS),
            'export_hook' => trim((string) ($payload['export_hook'] ?? 'csv')) ?: 'csv',
            'private_notes' => trim((string) ($payload['private_notes'] ?? '')) ?: null,
        ], true);
        service('auditLogger')->record('admissions.list.created', ['target_type' => 'admission_list_publication', 'target_id' => $id, 'summary' => 'Admissions staff created an admission list draft.', 'metadata' => ['version_number' => $version]]);

        return $id;
    }

    public function addOfferedApplication(int $publicationId, int $offerId, ?int $position = null): int
    {
        $this->assertAuthority('admissions.lists.preview');
        $publication = $this->publication($publicationId, false);
        if ($publication['status'] !== 'draft') {
            throw new InvalidArgumentException('Only draft admission lists can be changed.');
        }
        $offer = (new AdmissionOfferModel())->find($offerId);
        if ($offer === null || ! in_array($offer['offer_status'], self::ACTIVE_OFFER_STATUSES, true)) {
            throw new InvalidArgumentException('Only active admission offers can be listed.');
        }
        $application = (new ApplicantApplicationModel())->find((int) $offer['applicant_application_id']);
        if ($application === null || (int) $application['admission_cycle_id'] !== (int) $publication['admission_cycle_id'] || (int) $application['id'] !== (int) $offer['applicant_application_id']) {
            throw new InvalidArgumentException('Admission list entries must reference offered applications from the same tenant cycle.');
        }
        if (! empty($publication['programme_opening_id']) && (int) $publication['programme_opening_id'] !== (int) $application['programme_opening_id']) {
            throw new InvalidArgumentException('This offered application does not belong to the publication programme scope.');
        }

        $payload = array_merge($this->safeEntrySnapshot($application, $offer), ['admission_list_publication_id' => $publicationId, 'entry_position' => $position]);
        $model = new AdmissionListEntryModel();
        $existing = $model->where('admission_list_publication_id', $publicationId)->where('applicant_application_id', $application['id'])->first();
        $id = $existing === null ? (int) $model->insert($payload, true) : (int) $existing['id'];
        if ($existing !== null) {
            $model->update($id, $payload);
        }
        service('auditLogger')->record('admissions.list.entry_added', ['target_type' => 'admission_list_publication', 'target_id' => $publicationId, 'summary' => 'Admissions staff added an offered applicant to a list draft.', 'metadata' => ['offer_id' => $offerId]]);

        return $id;
    }

    /** @return array<string, mixed> */
    public function preview(int $publicationId): array
    {
        $this->assertAuthority('admissions.lists.preview');
        $publication = $this->publication($publicationId, false);

        return ['publication' => $publication, 'entries' => $this->entries($publicationId), 'safeFields' => self::SAFE_PUBLIC_FIELDS];
    }

    public function publish(int $publicationId): int
    {
        $this->assertAuthority('admissions.lists.publish');
        $publication = $this->publication($publicationId, false);
        if ($publication['status'] === 'published') {
            return (int) $publication['id'];
        }
        $entries = $this->entries($publicationId);
        if ($entries === []) {
            throw new InvalidArgumentException('Admission list cannot be published without offered applicants.');
        }
        $now = Time::now()->toDateTimeString();
        (new AdmissionListPublicationModel())->update($publicationId, ['status' => 'published', 'published_at' => $now, 'published_by' => service('tenantAccess')->currentUserId()]);
        service('publicWebsiteCache')->forgetMany(['admissions.published-lists', 'admissions.published-list.' . $publication['public_token']]);
        service('auditLogger')->record('admissions.list.published', ['target_type' => 'admission_list_publication', 'target_id' => $publicationId, 'summary' => 'Admissions staff published an admission list.', 'metadata' => ['entry_count' => count($entries)]]);
        service('admissionNotificationDispatcher')->queue('admissions.list.published', null, ['publication_id' => $publicationId, 'entry_count' => count($entries)]);

        return $publicationId;
    }

    /** @return list<array<string, mixed>> */
    public function publicLists(): array
    {
        return service('publicWebsiteCache')->remember('admissions.published-lists', fn (): array => (new AdmissionListPublicationModel())->where('status', 'published')->orderBy('published_at', 'DESC')->findAll(20));
    }

    /** @return array<string, mixed> */
    public function publicList(string $token): array
    {
        return service('publicWebsiteCache')->remember('admissions.published-list.' . $token, function () use ($token): array {
            $publication = (new AdmissionListPublicationModel())->where('public_token', $token)->where('status', 'published')->first();
            if ($publication === null) {
                throw new InvalidArgumentException('Published admission list was not found.');
            }

            return ['publication' => $publication, 'entries' => $this->entries((int) $publication['id'])];
        });
    }

    /** @return list<array<string, mixed>> */
    public function applicantPublishedEntries(): array
    {
        $profile = service('applicantAccessPolicy')->currentProfile(service('tenantContextManager')->current());
        if ($profile === null) {
            return [];
        }
        $applications = (new ApplicantApplicationModel())->where('applicant_profile_id', $profile['id'])->findAll();
        $ids = array_map(static fn (array $row): int => (int) $row['id'], $applications);
        if ($ids === []) {
            return [];
        }

        return (new AdmissionListEntryModel())
            ->select('admission_list_entries.*, admission_list_publications.title, admission_list_publications.public_token')
            ->join('admission_list_publications', 'admission_list_publications.id = admission_list_entries.admission_list_publication_id')
            ->whereIn('admission_list_entries.applicant_application_id', $ids)
            ->where('admission_list_publications.status', 'published')
            ->findAll();
    }

    /** @return array<string, int> */
    public function metrics(): array
    {
        $this->assertAuthority('admissions.lists.preview');

        return [
            'draft' => (new AdmissionListPublicationModel())->where('status', 'draft')->countAllResults(),
            'published' => (new AdmissionListPublicationModel())->where('status', 'published')->countAllResults(),
        ];
    }

    /** @return array<string, mixed> */
    private function publication(int $publicationId, bool $mustBePublished): array
    {
        $model = new AdmissionListPublicationModel();
        if ($mustBePublished) {
            $model->where('status', 'published');
        }
        $publication = $model->find($publicationId);
        if ($publication === null) {
            throw new InvalidArgumentException('Admission list publication was not found for this tenant.');
        }

        return $publication;
    }

    /** @return list<array<string, mixed>> */
    private function entries(int $publicationId): array
    {
        return (new AdmissionListEntryModel())->where('admission_list_publication_id', $publicationId)->orderBy('entry_position', 'ASC')->orderBy('applicant_display_name', 'ASC')->findAll();
    }

    /** @param array<string, mixed> $application @param array<string, mixed> $offer @return array<string, mixed> */
    private function safeEntrySnapshot(array $application, array $offer): array
    {
        $biodata = (new ApplicationBiodataDraftModel())->where('applicant_application_id', $application['id'])->first() ?? [];
        $programme = null;
        if (! empty($application['programme_opening_id'])) {
            $opening = (new \App\Models\Tenant\Admissions\AdmissionProgrammeOpeningModel())->find((int) $application['programme_opening_id']);
            $programme = $opening === null ? null : (new ProgrammeModel())->find((int) $opening['programme_id']);
        }
        $displayName = trim((string) (($biodata['surname'] ?? '') . ' ' . ($biodata['first_name'] ?? '')));

        return [
            'applicant_application_id' => $application['id'],
            'admission_offer_id' => $offer['id'],
            'application_number' => (string) ($application['application_number'] ?? ''),
            'applicant_display_name' => $displayName !== '' ? $displayName : 'Applicant #' . $application['id'],
            'programme_name' => $programme['name'] ?? null,
            'entry_status' => 'published',
        ];
    }

    private function assertAuthority(string $authority): void
    {
        $context = service('tenantContextManager')->current();
        if (! service('tenantAccess')->isMember($context) || ! service('tenantAccess')->hasAuthority($context, $authority)) {
            throw new InvalidArgumentException('You do not have authority to manage admission list publication.');
        }
    }
}

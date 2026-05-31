<?php

namespace App\Services\Website;

use App\Models\Tenant\DepartmentModel;
use App\Models\Tenant\ProgrammeModel;
use App\Models\Tenant\Website\AdmissionInformationPageModel;
use App\Models\Tenant\Website\DepartmentPublicProfileModel;
use App\Models\Tenant\Website\MediaFileModel;
use App\Models\Tenant\Website\ProgrammePublicProfileModel;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;

/**
 * Owns authorized showcase mutations and their audit/cache side effects.
 *
 * Controllers validate request shape, but this service re-checks ownership and
 * publication rules because CLI tools and future CMS clients may call it too.
 */
class ShowcaseManagementService
{
    private const STATUSES = ['draft', 'published', 'archived'];

    /** @return array{departments: list<array<string, mixed>>, profiles: list<array<string, mixed>>} */
    public function departmentFormData(?int $profileId = null): array
    {
        $model = new DepartmentPublicProfileModel();

        return [
            'departments' => (new DepartmentModel())->orderBy('name')->findAll(),
            'profiles' => $model->orderBy('slug')->findAll(),
            'selectedProfile' => $profileId === null ? null : $model->find($profileId),
        ];
    }

    /** @return array{programmes: list<array<string, mixed>>, profiles: list<array<string, mixed>>} */
    public function programmeFormData(?int $profileId = null): array
    {
        $model = new ProgrammePublicProfileModel();

        return [
            'programmes' => (new ProgrammeModel())->orderBy('name')->findAll(),
            'profiles' => $model->orderBy('slug')->findAll(),
            'selectedProfile' => $profileId === null ? null : $model->find($profileId),
        ];
    }

    /** @return array<string, mixed>|null */
    public function admissionFormData(): ?array
    {
        return (new AdmissionInformationPageModel())->first();
    }

    /** @param array<string, mixed> $payload */
    public function saveDepartment(array $payload): int
    {
        $department = (new DepartmentModel())->find((int) $payload['department_id']);
        if ($department === null) {
            throw new InvalidArgumentException('Invalid tenant department reference.');
        }

        $model = new DepartmentPublicProfileModel();
        $existing = $model->where('department_id', $department['id'])->first();
        $this->preparePublication($payload);
        $this->assertUniqueSlug($model, (string) $payload['slug'], $existing['id'] ?? null);
        $this->assertMedia($payload['featured_media_id'] ?? null, $payload['status'] === 'published');
        $id = $this->upsert($model, $existing, $payload);
        $this->audit('website.department_profile', 'department_public_profile', $id, $payload);
        $this->invalidate([
            'showcase.departments',
            'showcase.department.' . ($existing['slug'] ?? ''),
            'showcase.department.' . $payload['slug'],
            'showcase.programmes',
            'showcase.programmes.department.' . ($existing['slug'] ?? ''),
            'showcase.programmes.department.' . $payload['slug'],
        ]);

        return $id;
    }

    /** @param array<string, mixed> $payload */
    public function saveProgramme(array $payload): int
    {
        $programme = (new ProgrammeModel())->find((int) $payload['programme_id']);
        if ($programme === null || (new DepartmentModel())->find((int) $programme['department_id']) === null) {
            throw new InvalidArgumentException('Invalid tenant programme reference.');
        }

        $model = new ProgrammePublicProfileModel();
        $existing = $model->where('programme_id', $programme['id'])->first();
        $this->preparePublication($payload);
        $this->assertUniqueSlug($model, (string) $payload['slug'], $existing['id'] ?? null);
        $this->assertMedia($payload['featured_media_id'] ?? null, $payload['status'] === 'published');
        $id = $this->upsert($model, $existing, $payload);
        $departmentProfile = (new DepartmentPublicProfileModel())->where('department_id', $programme['department_id'])->first();
        $this->audit('website.programme_profile', 'programme_public_profile', $id, $payload);
        $this->invalidate([
            'showcase.programmes',
            'showcase.programmes.department.' . ($departmentProfile['slug'] ?? ''),
            'showcase.department.' . ($departmentProfile['slug'] ?? ''),
            'showcase.programme.' . ($existing['slug'] ?? ''),
            'showcase.programme.' . $payload['slug'],
        ]);

        return $id;
    }

    /** @param array<string, mixed> $payload */
    public function saveAdmissions(array $payload): int
    {
        $model = new AdmissionInformationPageModel();
        $existing = $model->first();
        $payload['slug'] = 'admissions';
        $this->preparePublication($payload);
        if (! empty($payload['application_url']) && ! $this->safeHttpUrl((string) $payload['application_url'])) {
            throw new InvalidArgumentException('Application URL must use HTTP or HTTPS.');
        }
        $id = $this->upsert($model, $existing, $payload);
        $this->audit('website.admission_information', 'admission_information_page', $id, $payload);
        $this->invalidate(['showcase.admissions']);

        return $id;
    }

    /** @param array<string, mixed> $payload */
    private function preparePublication(array &$payload): void
    {
        $status = (string) ($payload['status'] ?? 'draft');
        if (! in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException('Invalid public showcase status.');
        }

        $context = service('tenantContextManager')->current();
        if ($status === 'archived' && ! service('tenantAccess')->hasAuthority($context, 'website.content.archive')) {
            throw new InvalidArgumentException('Archiving requires website.content.archive authority.');
        }
        if ($status !== 'published') {
            $payload['published_at'] = null;
            $payload['published_by'] = null;
            return;
        }
        if (! service('tenantAccess')->hasAuthority($context, 'website.content.publish')) {
            throw new InvalidArgumentException('Publishing requires website.content.publish authority.');
        }

        $payload['published_at'] = Time::now()->toDateTimeString();
        $payload['published_by'] = service('tenantAccess')->currentUserId();
    }

    private function assertMedia(mixed $mediaId, bool $requirePublic): void
    {
        if ($mediaId === null || $mediaId === '') {
            return;
        }

        $media = (new MediaFileModel())->find((int) $mediaId);
        if ($media === null) {
            throw new InvalidArgumentException('Featured media must be owned by this tenant.');
        }
        if ($requirePublic && $media['visibility'] !== 'public') {
            throw new InvalidArgumentException('Published featured media must use public visibility.');
        }
    }

    private function assertUniqueSlug(object $model, string $slug, mixed $existingId): void
    {
        $match = $model->where('slug', $slug)->first();
        if ($match !== null && (int) $match['id'] !== (int) $existingId) {
            throw new InvalidArgumentException('Slug is already in use for this tenant.');
        }
    }

    /** @param object $model @param array<string, mixed>|null $existing @param array<string, mixed> $payload */
    private function upsert(object $model, ?array $existing, array $payload): int
    {
        if ($existing === null) {
            $context = service('tenantContextManager')->current();
            if (! service('tenantAccess')->hasAuthority($context, 'website.content.create')) {
                throw new InvalidArgumentException('Creating showcase content requires website.content.create authority.');
            }

            return (int) $model->insert($payload, true);
        }

        $model->update((int) $existing['id'], $payload);

        return (int) $existing['id'];
    }

    /** @param array<string, mixed> $payload */
    private function audit(string $action, string $targetType, int $id, array $payload): void
    {
        $details = [
            'target_type' => $targetType,
            'target_id' => $id,
            'summary' => 'Public showcase content updated.',
            'metadata' => ['status' => $payload['status'], 'fields' => array_keys($payload)],
        ];
        service('auditLogger')->record($action . '.updated', $details);

        if (in_array($payload['status'], ['published', 'archived'], true)) {
            // Keep lifecycle transitions separately searchable while retaining
            // the required update event for general showcase audit reports.
            service('auditLogger')->record($action . '.' . $payload['status'], $details);
        }
    }

    /** @param list<string> $keys */
    private function invalidate(array $keys): void
    {
        foreach (array_filter($keys) as $key) {
            service('publicWebsiteCache')->forget($key);
        }
    }

    private function safeHttpUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
    }
}

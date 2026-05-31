<?php

namespace App\Services\Website;

use App\Models\DepartmentIdentityModel;
use App\Models\Tenant\DepartmentModel;
use App\Models\Tenant\ProgrammeModel;
use App\Models\Tenant\Website\AdmissionInformationPageModel;
use App\Models\Tenant\Website\DepartmentPublicProfileModel;
use App\Models\Tenant\Website\ProgrammePublicProfileModel;
use InvalidArgumentException;

/**
 * Builds tenant-scoped public academic showcase view models.
 *
 * Public extension records are loaded separately from operational academic
 * records. This makes the active + published rule visible and auditable instead
 * of relying on a fragile cross-table query that could omit a tenant predicate.
 */
class PublicShowcaseService
{
    /** @return list<array<string, mixed>> */
    public function departments(): array
    {
        return service('publicWebsiteCache')->remember('showcase.departments', function (): array {
            $items = [];
            foreach ((new DepartmentPublicProfileModel())->where('status', 'published')->orderBy('slug', 'ASC')->findAll() as $profile) {
                $department = (new DepartmentModel())->find((int) $profile['department_id']);
                if ($department === null || (int) $department['is_active'] !== 1) {
                    continue;
                }

                $items[] = $this->departmentViewModel($department, $profile);
            }

            return $items;
        });
    }

    /** @return array<string, mixed> */
    public function department(string $slug): array
    {
        return service('publicWebsiteCache')->remember('showcase.department.' . $slug, function () use ($slug): array {
            $profile = (new DepartmentPublicProfileModel())->where('slug', $slug)->where('status', 'published')->first();
            if ($profile === null) {
                throw new InvalidArgumentException('Published department was not found.');
            }

            $department = (new DepartmentModel())->find((int) $profile['department_id']);
            if ($department === null || (int) $department['is_active'] !== 1) {
                throw new InvalidArgumentException('Published department was not found.');
            }

            $viewModel = $this->departmentViewModel($department, $profile);
            $viewModel['programmes'] = $this->programmesForDepartment((int) $department['id']);

            return $viewModel;
        });
    }

    /** @return list<array<string, mixed>> */
    public function programmes(?string $departmentSlug = null): array
    {
        $cacheKey = 'showcase.programmes' . ($departmentSlug === null ? '' : '.department.' . $departmentSlug);

        return service('publicWebsiteCache')->remember($cacheKey, function () use ($departmentSlug): array {
            $departmentId = null;
            if ($departmentSlug !== null && $departmentSlug !== '') {
                $department = $this->department($departmentSlug);
                $departmentId = (int) $department['id'];
            }

            return $this->programmesForDepartment($departmentId);
        });
    }

    /** @return array<string, mixed> */
    public function programme(string $slug): array
    {
        return service('publicWebsiteCache')->remember('showcase.programme.' . $slug, function () use ($slug): array {
            $profile = (new ProgrammePublicProfileModel())->where('slug', $slug)->where('status', 'published')->first();
            if ($profile === null) {
                throw new InvalidArgumentException('Published programme was not found.');
            }

            $programme = (new ProgrammeModel())->find((int) $profile['programme_id']);
            if ($programme === null || (int) $programme['is_active'] !== 1) {
                throw new InvalidArgumentException('Published programme was not found.');
            }

            return $this->programmeViewModel($programme, $profile);
        });
    }

    /** @return array<string, mixed>|null */
    public function admissionInformation(): ?array
    {
        return service('publicWebsiteCache')->remember('showcase.admissions', static function (): ?array {
            return (new AdmissionInformationPageModel())->where('status', 'published')->first();
        });
    }

    /** @return list<array<string, mixed>> */
    private function programmesForDepartment(?int $departmentId): array
    {
        $items = [];
        foreach ((new ProgrammePublicProfileModel())->where('status', 'published')->orderBy('slug', 'ASC')->findAll() as $profile) {
            $programme = (new ProgrammeModel())->find((int) $profile['programme_id']);
            if ($programme === null || (int) $programme['is_active'] !== 1) {
                continue;
            }
            if ($departmentId !== null && (int) $programme['department_id'] !== $departmentId) {
                continue;
            }

            try {
                $items[] = $this->programmeViewModel($programme, $profile);
            } catch (InvalidArgumentException) {
                // A programme whose owning department is inactive is hidden
                // rather than allowing one stale record to break the listing.
                continue;
            }
        }

        return $items;
    }

    /** @param array<string, mixed> $department @param array<string, mixed> $profile @return array<string, mixed> */
    private function departmentViewModel(array $department, array $profile): array
    {
        $identity = (new DepartmentIdentityModel())->where('department_id', $department['id'])->first();

        return array_merge($profile, $department, [
            'profile_id' => $profile['id'],
            'slug' => $profile['slug'],
            'summary' => $profile['summary'],
            'body' => $profile['body'],
            'featured_media_id' => $profile['featured_media_id'],
            'color_hex' => $identity['color_hex'] ?? $department['color_hex'] ?? service('themeResolver')->tenantTheme()['primary_color'],
            'href' => service('publicWebsiteUrl')->route('department', [$profile['slug']]),
        ]);
    }

    /** @param array<string, mixed> $programme @param array<string, mixed> $profile @return array<string, mixed> */
    private function programmeViewModel(array $programme, array $profile): array
    {
        $department = (new DepartmentModel())->find((int) $programme['department_id']);
        if ($department === null || (int) $department['is_active'] !== 1) {
            throw new InvalidArgumentException('Programme department is not publicly available.');
        }
        $identity = (new DepartmentIdentityModel())->where('department_id', $department['id'])->first();

        return array_merge($profile, $programme, [
            'profile_id' => $profile['id'],
            'slug' => $profile['slug'],
            'summary' => $profile['summary'],
            'body' => $profile['body'],
            'featured_media_id' => $profile['featured_media_id'],
            'department' => $department,
            'department_color' => $identity['color_hex'] ?? $department['color_hex'] ?? service('themeResolver')->tenantTheme()['primary_color'],
            'href' => service('publicWebsiteUrl')->route('programme', [$profile['slug']]),
        ]);
    }
}

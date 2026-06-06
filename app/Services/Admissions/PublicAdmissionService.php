<?php

namespace App\Services\Admissions;

use App\Models\Tenant\Admissions\AdmissionCycleModel;
use App\Models\Tenant\Admissions\AdmissionDocumentRequirementModel;
use App\Models\Tenant\Admissions\AdmissionProgrammeOpeningModel;
use App\Models\Tenant\Admissions\AdmissionRequirementDefinitionModel;
use App\Models\Tenant\Admissions\AdmissionSubjectRequirementModel;
use App\Models\Tenant\LevelModel;
use App\Models\Tenant\ProgrammeModel;
use CodeIgniter\I18n\Time;

/** Resolves whitelisted public admission configuration for the active tenant. */
class PublicAdmissionService
{
    /** @return array<string, mixed>|null */
    public function activeCycle(): ?array
    {
        return service('publicWebsiteCache')->remember('admissions.active', function (): ?array {
            $now = Time::now()->toDateTimeString();
            $cycle = (new AdmissionCycleModel())
                ->where('status', 'open')
                ->where('is_public', 1)
                ->where('opens_at <=', $now)
                ->where('closes_at >=', $now)
                ->orderBy('opens_at', 'DESC')
                ->first();

            return $cycle === null ? null : $this->only($cycle, ['id', 'title', 'code', 'opens_at', 'closes_at', 'instructions', 'screening_instructions']);
        });
    }

    /** @return list<array<string, mixed>> */
    public function openProgrammes(?int $cycleId = null): array
    {
        $cycle = $this->activeCycle();
        if ($cycle === null || ($cycleId !== null && $cycleId !== (int) $cycle['id'])) {
            return [];
        }

        return service('publicWebsiteCache')->remember('admissions.open-programmes', function () use ($cycle): array {
            $rows = (new AdmissionProgrammeOpeningModel())
                ->where('admission_cycle_id', $cycle['id'])
                ->where('status', 'open')
                ->orderBy('sort_order')
                ->findAll();
            $visible = [];
            foreach ($rows as $row) {
                $programme = (new ProgrammeModel())->find((int) $row['programme_id']);
                if ($programme === null || (int) ($programme['is_active'] ?? 0) !== 1) {
                    continue;
                }
                $level = empty($row['entry_level_id']) ? null : (new LevelModel())->find((int) $row['entry_level_id']);
                // Public DTOs are explicit. Never return tenant IDs, actor IDs,
                // or soft-delete metadata merely because a view ignores them.
                $visible[] = array_merge($this->only($row, ['id', 'admission_cycle_id', 'application_quota', 'screening_method', 'instructions', 'requirement_summary', 'sort_order']), [
                    'programme_name' => $programme['name'],
                    'programme_code' => $programme['code'],
                    'entry_level_name' => $level['name'] ?? null,
                ]);
            }

            return $visible;
        });
    }

    /** @return array<string, mixed>|null */
    public function programme(int $id): ?array
    {
        foreach ($this->openProgrammes() as $programme) {
            if ((int) $programme['id'] !== $id) {
                continue;
            }
            $programme['requirements'] = array_map(fn (array $row): array => $this->only($row, ['requirement_type', 'code', 'label', 'description', 'is_required', 'sort_order']), $this->definitions((int) $programme['admission_cycle_id'], $id));
            $programme['subject_requirements'] = array_map(fn (array $row): array => $this->only($row, ['subject_code', 'subject_name', 'minimum_grade', 'requirement_group', 'is_required', 'sort_order']), $this->subjects((int) $programme['admission_cycle_id'], $id));
            $programme['document_requirements'] = array_map(fn (array $row): array => $this->only($row, ['document_type', 'label', 'is_required', 'sort_order']), $this->documents((int) $programme['admission_cycle_id'], $id));

            return $programme;
        }

        return null;
    }

    /** @return list<array<string, mixed>> */
    private function definitions(int $cycleId, int $openingId): array
    {
        return (new AdmissionRequirementDefinitionModel())->where('admission_cycle_id', $cycleId)->groupStart()->where('programme_opening_id', null)->orWhere('programme_opening_id', $openingId)->groupEnd()->where('status', 'active')->orderBy('sort_order')->findAll();
    }

    /** @return list<array<string, mixed>> */
    private function subjects(int $cycleId, int $openingId): array
    {
        return (new AdmissionSubjectRequirementModel())->where('admission_cycle_id', $cycleId)->groupStart()->where('programme_opening_id', null)->orWhere('programme_opening_id', $openingId)->groupEnd()->where('status', 'active')->orderBy('sort_order')->findAll();
    }

    /** @return list<array<string, mixed>> */
    private function documents(int $cycleId, int $openingId): array
    {
        return (new AdmissionDocumentRequirementModel())->where('admission_cycle_id', $cycleId)->groupStart()->where('programme_opening_id', null)->orWhere('programme_opening_id', $openingId)->groupEnd()->where('status', 'active')->orderBy('sort_order')->findAll();
    }

    /** @param array<string, mixed> $row @param list<string> $keys @return array<string, mixed> */
    private function only(array $row, array $keys): array
    {
        return array_intersect_key($row, array_flip($keys));
    }
}

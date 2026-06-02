<?php

namespace App\Services\Admissions;

use App\Models\Tenant\AcademicSessionModel;
use App\Models\Tenant\Admissions\AdmissionCycleModel;
use App\Models\Tenant\Admissions\AdmissionDocumentRequirementModel;
use App\Models\Tenant\Admissions\AdmissionProgrammeOpeningModel;
use App\Models\Tenant\Admissions\AdmissionRequirementDefinitionModel;
use App\Models\Tenant\Admissions\AdmissionSubjectRequirementModel;
use App\Models\Tenant\DepartmentModel;
use App\Models\Tenant\LevelModel;
use App\Models\Tenant\ProgrammeModel;
use Config\Admissions;
use InvalidArgumentException;

/** Owns tenant-safe admission setup mutations, lifecycle checks, and audits. */
class AdmissionConfigurationService
{
    private const CYCLE_STATUSES = ['draft', 'scheduled', 'open', 'closed', 'under_review', 'admission_published', 'archived'];
    private const PROGRAMME_STATUSES = ['open', 'closed', 'hidden', 'full', 'suspended'];
    private const REQUIREMENT_STATUSES = ['active', 'archived'];
    private const REQUIREMENT_TYPES = ['biodata', 'olevel', 'document', 'other'];
    private const SCREENING_METHODS = ['manual_review', 'physical_screening', 'online_screening', 'exam', 'interview', 'combined'];
    private const CYCLE_TRANSITIONS = [
        'draft' => ['scheduled', 'open', 'archived'],
        'scheduled' => ['draft', 'open', 'closed', 'archived'],
        'open' => ['closed'],
        'closed' => ['under_review', 'archived'],
        'under_review' => ['admission_published', 'archived'],
        'admission_published' => ['archived'],
        'archived' => [],
    ];

    /** @return array<string, mixed> */
    public function workspace(): array
    {
        $cycles = (new AdmissionCycleModel())->orderBy('opens_at', 'DESC')->findAll();
        $openings = (new AdmissionProgrammeOpeningModel())->orderBy('sort_order')->findAll();

        return [
            'cycles' => $cycles,
            'programmeOpenings' => $openings,
            'requirements' => (new AdmissionRequirementDefinitionModel())->orderBy('sort_order')->findAll(),
            'subjectRequirements' => (new AdmissionSubjectRequirementModel())->orderBy('sort_order')->findAll(),
            'documentRequirements' => (new AdmissionDocumentRequirementModel())->orderBy('sort_order')->findAll(),
            'academicSessions' => (new AcademicSessionModel())->orderBy('name', 'DESC')->findAll(),
            'programmes' => (new ProgrammeModel())->orderBy('name')->findAll(),
            'departments' => (new DepartmentModel())->orderBy('name')->findAll(),
            'levels' => (new LevelModel())->orderBy('sort_order')->findAll(),
        ];
    }

    /** @param array<string, mixed> $payload */
    public function saveCycle(array $payload): int
    {
        $this->assertAuthority('admissions.cycles.manage');
        $session = (new AcademicSessionModel())->find((int) $payload['academic_session_id']);
        if ($session === null || ($session['status'] ?? 'active') !== 'active') {
            throw new InvalidArgumentException('Select an active tenant academic session.');
        }
        if (strtotime((string) $payload['closes_at']) <= strtotime((string) $payload['opens_at'])) {
            throw new InvalidArgumentException('Admission closing time must be after opening time.');
        }

        $payload['status'] = $payload['status'] ?? 'draft';
        $this->assertOption((string) $payload['status'], self::CYCLE_STATUSES, 'Unsupported admission cycle status.');
        $existing = ! empty($payload['id']) ? (new AdmissionCycleModel())->find((int) $payload['id']) : null;
        if (! empty($payload['id']) && $existing === null) {
            throw new InvalidArgumentException('Admission cycle was not found in this tenant.');
        }
        if ($existing !== null && $existing['status'] !== $payload['status']) {
            $this->assertCycleTransition((string) $existing['status'], (string) $payload['status']);
        }
        unset($payload['id']);

        $model = new AdmissionCycleModel();
        $id = $existing === null ? (int) $model->insert($payload, true) : (int) $existing['id'];
        if ($existing !== null) {
            $model->update($id, $payload);
        }
        $event = $existing === null ? 'admissions.cycle.created' : ($existing['status'] === $payload['status'] ? 'admissions.cycle.updated' : $this->cycleStatusEvent((string) $payload['status']));
        $this->audit($event, 'admission_cycle', $id, ['status' => $payload['status']]);
        $this->invalidatePublic();

        return $id;
    }

    public function transitionCycle(int $id, string $status): void
    {
        $this->assertAuthority('admissions.cycles.manage');
        $cycle = $this->cycle($id);
        $this->assertOption($status, self::CYCLE_STATUSES, 'Unsupported admission cycle status.');
        $this->assertCycleTransition((string) $cycle['status'], $status);
        (new AdmissionCycleModel())->update($id, ['status' => $status]);
        $this->audit($this->cycleStatusEvent($status), 'admission_cycle', $id, ['from' => $cycle['status'], 'to' => $status]);
        $this->invalidatePublic();
    }

    /** @param array<string, mixed> $payload */
    public function saveProgrammeOpening(array $payload): int
    {
        $this->assertAuthority('admissions.programmes.manage');
        $cycle = $this->cycle((int) $payload['admission_cycle_id']);
        if ($cycle['status'] === 'archived') {
            throw new InvalidArgumentException('Archived admission cycles cannot accept programme changes.');
        }
        $programme = (new ProgrammeModel())->find((int) $payload['programme_id']);
        if ($programme === null || (int) ($programme['is_active'] ?? 0) !== 1) {
            throw new InvalidArgumentException('Select an active tenant programme.');
        }
        $department = (new DepartmentModel())->find((int) $payload['department_id']);
        if ($department === null || (int) $department['id'] !== (int) $programme['department_id']) {
            throw new InvalidArgumentException('Programme and department must belong to the same tenant relationship.');
        }
        if (! empty($payload['entry_level_id']) && (new LevelModel())->find((int) $payload['entry_level_id']) === null) {
            throw new InvalidArgumentException('Select a valid tenant entry level.');
        }
        $payload['status'] = $payload['status'] ?? 'open';
        $payload['screening_method'] = $payload['screening_method'] ?? 'manual_review';
        $this->assertOption((string) $payload['status'], self::PROGRAMME_STATUSES, 'Unsupported programme admission status.');
        $this->assertOption((string) $payload['screening_method'], self::SCREENING_METHODS, 'Unsupported screening method.');
        $existing = ! empty($payload['id']) ? (new AdmissionProgrammeOpeningModel())->find((int) $payload['id']) : null;
        if (! empty($payload['id']) && $existing === null) {
            throw new InvalidArgumentException('Programme opening was not found in this tenant.');
        }
        unset($payload['id']);
        $model = new AdmissionProgrammeOpeningModel();
        $id = $existing === null ? (int) $model->insert($payload, true) : (int) $existing['id'];
        if ($existing !== null) {
            $model->update($id, $payload);
        }
        $event = $existing === null ? 'admissions.programme.opened' : ($existing['status'] === $payload['status'] ? 'admissions.programme.updated' : 'admissions.programme.status_changed');
        $this->audit($event, 'admission_programme_opening', $id, ['status' => $payload['status']]);
        $this->invalidatePublic();

        return $id;
    }

    /** @param array<string, mixed> $payload */
    public function saveRequirement(array $payload): int
    {
        $this->assertAuthority('admissions.requirements.manage');
        $this->assertRequirementScope($payload);
        $this->assertOption((string) $payload['requirement_type'], self::REQUIREMENT_TYPES, 'Unsupported requirement type.');
        $payload['status'] = $payload['status'] ?? 'active';
        $this->assertOption((string) $payload['status'], self::REQUIREMENT_STATUSES, 'Unsupported requirement status.');
        if (! empty($payload['configuration_json'])) {
            $configuration = json_decode((string) $payload['configuration_json'], true);
            if (! is_array($configuration)) {
                throw new InvalidArgumentException('Requirement configuration must be a valid JSON object.');
            }
        }

        $model = new AdmissionRequirementDefinitionModel();
        $existing = ! empty($payload['id']) ? $model->find((int) $payload['id']) : null;
        if (! empty($payload['id']) && $existing === null) { throw new InvalidArgumentException('Admission requirement was not found in this tenant.'); }
        unset($payload['id']);
        $id = $existing === null ? (int) $model->insert($payload, true) : (int) $existing['id'];
        if ($existing !== null) { $model->update($id, $payload); }
        $this->audit($existing === null ? 'admissions.requirement.created' : ($payload['status'] === 'archived' ? 'admissions.requirement.archived' : 'admissions.requirement.updated'), 'admission_requirement_definition', $id, ['type' => $payload['requirement_type']]);
        $this->invalidatePublic();

        return $id;
    }

    /** @param array<string, mixed> $payload */
    public function saveSubjectRequirement(array $payload): int
    {
        $this->assertAuthority('admissions.requirements.manage');
        $this->assertRequirementScope($payload);
        $payload['status'] = $payload['status'] ?? 'active';
        $this->assertOption((string) $payload['status'], self::REQUIREMENT_STATUSES, 'Unsupported subject requirement status.');
        $payload['subject_code'] = strtoupper(trim((string) $payload['subject_code']));
        $model = new AdmissionSubjectRequirementModel();
        $existing = ! empty($payload['id']) ? $model->find((int) $payload['id']) : null;
        if (! empty($payload['id']) && $existing === null) { throw new InvalidArgumentException('Subject requirement was not found in this tenant.'); }
        unset($payload['id']);
        $id = $existing === null ? (int) $model->insert($payload, true) : (int) $existing['id'];
        if ($existing !== null) { $model->update($id, $payload); }
        $this->audit($existing === null ? 'admissions.subject_requirement.created' : 'admissions.subject_requirement.updated', 'admission_subject_requirement', $id, ['subject_code' => $payload['subject_code']]);
        $this->invalidatePublic();

        return $id;
    }

    /** @param array<string, mixed> $payload */
    public function saveDocumentRequirement(array $payload): int
    {
        $this->assertAuthority('admissions.requirements.manage');
        $this->assertRequirementScope($payload);
        $payload['status'] = $payload['status'] ?? 'active';
        $this->assertOption((string) $payload['status'], self::REQUIREMENT_STATUSES, 'Unsupported document requirement status.');
        $allowed = array_values(array_filter(array_map('trim', explode(',', (string) $payload['allowed_mime_types']))));
        if ($allowed === [] || array_diff($allowed, array_keys(config(Admissions::class)->documentExtensionsByMimeType)) !== []) {
            throw new InvalidArgumentException('Document MIME types must use the supported private upload policy.');
        }
        if ((int) $payload['maximum_size_bytes'] > config(Admissions::class)->maximumDocumentSizeBytes) {
            throw new InvalidArgumentException('Document size exceeds the platform private upload policy.');
        }
        $payload['allowed_mime_types'] = implode(',', $allowed);
        $model = new AdmissionDocumentRequirementModel();
        $existing = ! empty($payload['id']) ? $model->find((int) $payload['id']) : null;
        if (! empty($payload['id']) && $existing === null) { throw new InvalidArgumentException('Document requirement was not found in this tenant.'); }
        unset($payload['id']);
        $id = $existing === null ? (int) $model->insert($payload, true) : (int) $existing['id'];
        if ($existing !== null) { $model->update($id, $payload); }
        $this->audit($existing === null ? 'admissions.document_requirement.created' : 'admissions.document_requirement.updated', 'admission_document_requirement', $id, ['document_type' => $payload['document_type']]);
        $this->invalidatePublic();

        return $id;
    }

    /** @return array<string, mixed> */
    private function cycle(int $id): array
    {
        $cycle = (new AdmissionCycleModel())->find($id);
        if ($cycle === null) {
            throw new InvalidArgumentException('Admission cycle was not found in this tenant.');
        }

        return $cycle;
    }

    /** @param array<string, mixed> $payload */
    private function assertRequirementScope(array $payload): void
    {
        $this->cycle((int) $payload['admission_cycle_id']);
        if (! empty($payload['programme_opening_id'])) {
            $opening = (new AdmissionProgrammeOpeningModel())->find((int) $payload['programme_opening_id']);
            if ($opening === null || (int) $opening['admission_cycle_id'] !== (int) $payload['admission_cycle_id']) {
                throw new InvalidArgumentException('Requirement programme opening must belong to the selected tenant admission cycle.');
            }
        }
    }

    private function cycleStatusEvent(string $status): string
    {
        return match ($status) {
            'open' => 'admissions.cycle.opened',
            'closed' => 'admissions.cycle.closed',
            'archived' => 'admissions.cycle.archived',
            default => 'admissions.cycle.' . $status,
        };
    }

    private function assertCycleTransition(string $from, string $to): void
    {
        if (! in_array($to, self::CYCLE_TRANSITIONS[$from] ?? [], true)) {
            throw new InvalidArgumentException('Unsupported admission cycle lifecycle transition.');
        }
    }

    private function assertAuthority(string $authority): void
    {
        $context = service('tenantContextManager')->current();
        if (! service('tenantAccess')->isMember($context) || ! service('tenantAccess')->hasAuthority($context, $authority)) {
            throw new InvalidArgumentException('You do not have authority to manage this admission configuration.');
        }
    }

    /** @param list<string> $allowed */
    private function assertOption(string $value, array $allowed, string $message): void
    {
        if (! in_array($value, $allowed, true)) {
            throw new InvalidArgumentException($message);
        }
    }

    /** @param array<string, mixed> $metadata */
    private function audit(string $action, string $targetType, int $targetId, array $metadata): void
    {
        service('auditLogger')->record($action, ['target_type' => $targetType, 'target_id' => $targetId, 'summary' => 'Admission configuration changed.', 'metadata' => $metadata]);
    }

    private function invalidatePublic(): void
    {
        service('publicWebsiteCache')->forgetMany(['admissions.active', 'admissions.open-programmes']);
    }
}

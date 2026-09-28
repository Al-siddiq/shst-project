<?php

namespace App\Services;

use App\Models\Tenant\AcademicSessionModel;
use App\Models\Tenant\CourseModel;
use App\Models\Tenant\DepartmentModel;
use App\Models\Tenant\LevelModel;
use App\Models\Tenant\ProgrammeCourseModel;
use App\Models\Tenant\ProgrammeModel;
use App\Models\Tenant\SemesterModel;
use App\Models\Tenant\TenantProfileModel;
use InvalidArgumentException;
use RuntimeException;

/** Owns Block 1 configuration mutations, tenant references, and mandatory audit. */
class TenantConfigurationService
{
    public function upsertProfile(array $payload): int
    {
        return service('transactional')->run(function () use ($payload): int {
            $model = new TenantProfileModel();
            $existing = $model->first();
            $id = $existing === null ? $model->insert($payload, true) : $existing['id'];
            $ok = $existing === null ? $id : $model->update((int) $id, $payload);
            $this->assertMutation($ok, 'Tenant profile');
            $this->audit($existing === null ? 'tenant.profile.create' : 'tenant.profile.update', 'tenant_profile', $id);
            return (int) $id;
        });
    }

    public function create(string $type, array $payload): int
    {
        $models = [
            'academic_session' => AcademicSessionModel::class, 'semester' => SemesterModel::class,
            'level' => LevelModel::class, 'department' => DepartmentModel::class,
        ];
        if (! isset($models[$type])) {
            throw new InvalidArgumentException('Unsupported institutional configuration type.');
        }
        return service('transactional')->run(function () use ($models, $type, $payload): int {
            $id = (new $models[$type]())->insert($payload, true);
            $this->assertMutation($id, ucfirst(str_replace('_', ' ', $type)));
            $this->audit('tenant.configuration.create', $type, $id);
            return (int) $id;
        });
    }

    public function createProgramme(array $payload): int
    {
        $this->requireTenantRecord(new DepartmentModel(), (int) $payload['department_id'], 'department');
        return $this->insertAudited(new ProgrammeModel(), $payload, 'tenant.programme.create', 'programme');
    }

    public function createCourse(array $payload): int
    {
        if (! empty($payload['department_id'])) {
            $this->requireTenantRecord(new DepartmentModel(), (int) $payload['department_id'], 'department');
        }
        return $this->insertAudited(new CourseModel(), $payload, 'tenant.course.create', 'course');
    }

    public function mapProgrammeCourse(array $payload): int
    {
        $this->requireTenantRecord(new ProgrammeModel(), (int) $payload['programme_id'], 'programme');
        $this->requireTenantRecord(new CourseModel(), (int) $payload['course_id'], 'course');
        if (! empty($payload['level_id'])) $this->requireTenantRecord(new LevelModel(), (int) $payload['level_id'], 'level');
        if (! empty($payload['semester_id'])) $this->requireTenantRecord(new SemesterModel(), (int) $payload['semester_id'], 'semester');
        return $this->insertAudited(new ProgrammeCourseModel(), $payload, 'tenant.programme_course.create', 'programme_course');
    }

    private function insertAudited(object $model, array $payload, string $action, string $target): int
    {
        return service('transactional')->run(function () use ($model, $payload, $action, $target): int {
            $id = $model->insert($payload, true);
            $this->assertMutation($id, ucfirst($target));
            $this->audit($action, $target, $id);
            return (int) $id;
        });
    }

    private function requireTenantRecord(object $model, int $id, string $label): void
    {
        if ($id < 1 || $model->find($id) === null) {
            throw new InvalidArgumentException('Invalid tenant ' . $label . ' reference.');
        }
    }

    private function audit(string $action, string $target, int|string $id): void
    {
        service('auditLogger')->record($action, ['target_type' => $target, 'target_id' => $id, 'summary' => 'Tenant configuration changed.']);
    }

    private function assertMutation(mixed $result, string $label): void
    {
        if ($result === false || $result === null || $result === 0 || $result === '0') {
            throw new RuntimeException($label . ' could not be persisted.');
        }
    }
}

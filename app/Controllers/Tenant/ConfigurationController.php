<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\Tenant\AcademicSessionModel;
use App\Models\Tenant\CourseModel;
use App\Models\Tenant\DepartmentModel;
use App\Models\Tenant\LevelModel;
use App\Models\Tenant\ProgrammeCourseModel;
use App\Models\Tenant\ProgrammeModel;
use App\Models\Tenant\SemesterModel;
use App\Models\Tenant\TenantProfileModel;
use App\Traits\ApiResponseTrait;

class ConfigurationController extends BaseController
{
    use ApiResponseTrait;

    public function profileUpsert()
    {
        $payload = $this->request->getJSON(true) ?? [];
        $rules = [
            'institution_name' => 'required|min_length[3]|max_length[200]',
            'official_email' => 'permit_empty|valid_email',
            'official_phone' => 'permit_empty|max_length[60]',
        ];

        if (! $this->validateData($payload, $rules)) {
            return $this->fail('Validation failed.', $this->validator->getErrors(), 422);
        }

        $model = new TenantProfileModel();
        $existing = $model->first();

        if ($existing) {
            $model->update($existing['id'], $payload);
            return $this->ok('Tenant profile updated.', ['id' => $existing['id']]);
        }

        $id = $model->insert($payload, true);
        return $this->ok('Tenant profile created.', ['id' => $id], 201);
    }

    public function createAcademicSession() { return $this->createEntity(new AcademicSessionModel(), ['name' => 'required|max_length[120]']); }
    public function createSemester() { return $this->createEntity(new SemesterModel(), ['name' => 'required|max_length[120]']); }
    public function createLevel() { return $this->createEntity(new LevelModel(), ['name' => 'required|max_length[120]']); }
    public function createDepartment() { return $this->createEntity(new DepartmentModel(), ['name' => 'required|max_length[160]','color_hex'=>'permit_empty|regex_match[/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/]']); }

    public function createProgramme()
    {
        $payload = $this->request->getJSON(true) ?? [];
        $rules = ['name' => 'required|max_length[200]', 'department_id' => 'required|integer'];

        if (! $this->validateData($payload, $rules)) {
            return $this->fail('Validation failed.', $this->validator->getErrors(), 422);
        }

        // Tenant isolation check: department must exist inside same tenant scope.
        if ((new DepartmentModel())->find((int) $payload['department_id']) === null) {
            return $this->fail('Validation failed.', ['department_id' => 'Invalid tenant department reference.'], 422);
        }

        $id = (new ProgrammeModel())->insert($payload, true);
        return $this->ok('Programme created.', ['id' => $id], 201);
    }

    public function createCourse()
    {
        $payload = $this->request->getJSON(true) ?? [];
        $rules = ['title' => 'required|max_length[200]', 'course_code' => 'required|max_length[50]'];

        if (! $this->validateData($payload, $rules)) {
            return $this->fail('Validation failed.', $this->validator->getErrors(), 422);
        }

        if (! empty($payload['department_id']) && (new DepartmentModel())->find((int) $payload['department_id']) === null) {
            return $this->fail('Validation failed.', ['department_id' => 'Invalid tenant department reference.'], 422);
        }

        $id = (new CourseModel())->insert($payload, true);
        return $this->ok('Course created.', ['id' => $id], 201);
    }

    public function mapProgrammeCourse()
    {
        $payload = $this->request->getJSON(true) ?? [];
        $rules = [
            'programme_id' => 'required|integer',
            'course_id' => 'required|integer',
            'level_id' => 'permit_empty|integer',
            'semester_id' => 'permit_empty|integer',
        ];

        if (! $this->validateData($payload, $rules)) {
            return $this->fail('Validation failed.', $this->validator->getErrors(), 422);
        }

        // Tenant-scoped FK checks prevent cross-tenant relationships.
        if ((new ProgrammeModel())->find((int) $payload['programme_id']) === null) {
            return $this->fail('Validation failed.', ['programme_id' => 'Invalid tenant programme reference.'], 422);
        }
        if ((new CourseModel())->find((int) $payload['course_id']) === null) {
            return $this->fail('Validation failed.', ['course_id' => 'Invalid tenant course reference.'], 422);
        }
        if (! empty($payload['level_id']) && (new LevelModel())->find((int) $payload['level_id']) === null) {
            return $this->fail('Validation failed.', ['level_id' => 'Invalid tenant level reference.'], 422);
        }
        if (! empty($payload['semester_id']) && (new SemesterModel())->find((int) $payload['semester_id']) === null) {
            return $this->fail('Validation failed.', ['semester_id' => 'Invalid tenant semester reference.'], 422);
        }

        $id = (new ProgrammeCourseModel())->insert($payload, true);
        return $this->ok('Programme course mapping created.', ['id' => $id], 201);
    }

    private function createEntity(object $model, array $rules)
    {
        $payload = $this->request->getJSON(true) ?? [];

        if (! $this->validateData($payload, $rules)) {
            return $this->fail('Validation failed.', $this->validator->getErrors(), 422);
        }

        $id = $model->insert($payload, true);

        return $this->ok('Record created.', ['id' => $id], 201);
    }
}

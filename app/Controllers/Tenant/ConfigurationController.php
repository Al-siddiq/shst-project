<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;
use InvalidArgumentException;

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

        $id = service('tenantConfiguration')->upsertProfile($this->validator->getValidated());
        return $this->ok('Tenant profile saved.', ['id' => $id]);
    }

    public function createAcademicSession() { return $this->createEntity('academic_session', ['name' => 'required|max_length[120]']); }
    public function createSemester() { return $this->createEntity('semester', ['name' => 'required|max_length[120]']); }
    public function createLevel() { return $this->createEntity('level', ['name' => 'required|max_length[120]']); }
    public function createDepartment() { return $this->createEntity('department', ['name' => 'required|max_length[160]','color_hex'=>'permit_empty|regex_match[/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/]']); }

    public function createProgramme()
    {
        $payload = $this->request->getJSON(true) ?? [];
        $rules = ['name' => 'required|max_length[200]', 'department_id' => 'required|integer'];

        if (! $this->validateData($payload, $rules)) {
            return $this->fail('Validation failed.', $this->validator->getErrors(), 422);
        }

        try { $id = service('tenantConfiguration')->createProgramme($this->validator->getValidated()); }
        catch (InvalidArgumentException $e) { return $this->fail('Validation failed.', ['department_id' => $e->getMessage()], 422); }
        return $this->ok('Programme created.', ['id' => $id], 201);
    }

    public function createCourse()
    {
        $payload = $this->request->getJSON(true) ?? [];
        $rules = ['title' => 'required|max_length[200]', 'course_code' => 'required|max_length[50]'];

        if (! $this->validateData($payload, $rules)) {
            return $this->fail('Validation failed.', $this->validator->getErrors(), 422);
        }

        try { $id = service('tenantConfiguration')->createCourse($this->validator->getValidated()); }
        catch (InvalidArgumentException $e) { return $this->fail('Validation failed.', ['department_id' => $e->getMessage()], 422); }
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

        try { $id = service('tenantConfiguration')->mapProgrammeCourse($this->validator->getValidated()); }
        catch (InvalidArgumentException $e) { return $this->fail('Validation failed.', ['reference' => $e->getMessage()], 422); }
        return $this->ok('Programme course mapping created.', ['id' => $id], 201);
    }

    private function createEntity(string $type, array $rules)
    {
        $payload = $this->request->getJSON(true) ?? [];

        if (! $this->validateData($payload, $rules)) {
            return $this->fail('Validation failed.', $this->validator->getErrors(), 422);
        }

        $id = service('tenantConfiguration')->create($type, $this->validator->getValidated());

        return $this->ok('Record created.', ['id' => $id], 201);
    }
}

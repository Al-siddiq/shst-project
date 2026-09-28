<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use InvalidArgumentException;

class AdministrationController extends BaseController
{
    public function dashboard() { return view('tenant/admin/dashboard', service('tenantAdministration')->dashboard()); }
    public function configuration() { return view('tenant/admin/configuration', service('tenantAdministration')->configuration()); }
    public function access() { return view('tenant/admin/access', service('tenantAdministration')->access()); }
    public function branding() { return view('tenant/admin/branding', service('tenantAdministration')->theme()); }
    public function audit() { return view('tenant/admin/audit', ['events' => service('tenantAdministration')->audit()]); }
    public function domains() { return view('tenant/admin/domains', ['domains' => service('tenantAdministration')->domains()]); }

    public function saveProfile() { return $this->validated(['institution_name' => 'required|min_length[3]|max_length[200]', 'short_name' => 'permit_empty|max_length[120]', 'official_email' => 'permit_empty|valid_email', 'official_phone' => 'permit_empty|max_length[60]', 'address' => 'permit_empty|max_length[255]', 'state' => 'permit_empty|max_length[120]', 'lga' => 'permit_empty|max_length[120]'], fn ($data) => service('tenantConfiguration')->upsertProfile($data), 'Institution profile saved.'); }
    public function createSession() { return $this->validated(['name' => 'required|max_length[120]', 'start_date' => 'permit_empty|valid_date[Y-m-d]', 'end_date' => 'permit_empty|valid_date[Y-m-d]', 'is_current' => 'permit_empty|in_list[0,1]', 'status' => 'permit_empty|in_list[active,inactive,closed]'], fn ($data) => service('tenantConfiguration')->create('academic_session', $data), 'Academic session created.'); }
    public function createSemester() { return $this->validated(['name' => 'required|max_length[120]', 'code' => 'permit_empty|max_length[30]'], fn ($data) => service('tenantConfiguration')->create('semester', $data), 'Semester created.'); }
    public function createLevel() { return $this->validated(['name' => 'required|max_length[120]', 'code' => 'permit_empty|max_length[40]', 'sort_order' => 'permit_empty|integer'], fn ($data) => service('tenantConfiguration')->create('level', $data), 'Level created.'); }
    public function createDepartment() { return $this->validated(['name' => 'required|max_length[160]', 'code' => 'permit_empty|max_length[40]', 'color_hex' => 'permit_empty|regex_match[/^#[A-Fa-f0-9]{6}$/]'], fn ($data) => service('tenantConfiguration')->create('department', $data), 'Department created.'); }
    public function createProgramme() { return $this->validated(['name' => 'required|max_length[200]', 'code' => 'permit_empty|max_length[40]', 'department_id' => 'required|integer', 'duration_years' => 'permit_empty|integer'], fn ($data) => service('tenantConfiguration')->createProgramme($data), 'Programme created.'); }
    public function createCourse() { return $this->validated(['title' => 'required|max_length[200]', 'course_code' => 'required|max_length[50]', 'department_id' => 'permit_empty|integer', 'credit_units' => 'permit_empty|integer'], fn ($data) => service('tenantConfiguration')->createCourse($data), 'Course created.'); }
    public function mapCourse() { return $this->validated(['programme_id' => 'required|integer', 'course_id' => 'required|integer', 'level_id' => 'permit_empty|integer', 'semester_id' => 'permit_empty|integer', 'is_required' => 'permit_empty|in_list[0,1]'], fn ($data) => service('tenantConfiguration')->mapProgrammeCourse($data), 'Programme-course mapping saved.'); }
    public function createAuthority() { return $this->validated(['code' => 'required|regex_match[/^[a-z0-9._-]+$/]|max_length[120]', 'name' => 'required|max_length[160]', 'description' => 'permit_empty|max_length[500]'], fn ($data) => service('tenantAdministration')->createAuthority($data), 'Operational authority created.'); }
    public function grantAuthority() { return $this->validated(['membership_id' => 'required|integer', 'authority_id' => 'required|integer'], fn ($data) => service('tenantAdministration')->grantAuthority((int) $data['membership_id'], (int) $data['authority_id']), 'Authority granted.'); }
    public function membershipLifecycle(int $id) { return $this->validated(['status' => 'required|in_list[active,suspended,revoked]', 'reason' => 'required|min_length[10]|max_length[500]'], fn ($data) => service('tenantAdministration')->transitionMembership($id, $data['status'], $data['reason']), 'Account lifecycle updated.'); }
    public function saveTheme() { return $this->validated(['primary_color' => 'required|regex_match[/^#[A-Fa-f0-9]{6}$/]', 'secondary_color' => 'required|regex_match[/^#[A-Fa-f0-9]{6}$/]', 'accent_color' => 'required|regex_match[/^#[A-Fa-f0-9]{6}$/]', 'logo_path' => 'permit_empty|max_length[255]'], fn ($data) => service('tenantAdministration')->saveTheme($data), 'School branding saved.'); }
    public function saveDepartmentIdentity() { return $this->validated(['department_id' => 'required|integer', 'color_hex' => 'required|regex_match[/^#[A-Fa-f0-9]{6}$/]', 'icon_key' => 'permit_empty|alpha_dash|max_length[80]'], fn ($data) => service('tenantAdministration')->saveDepartmentIdentity($data), 'Department identity saved.'); }

    private function validated(array $rules, callable $action, string $message)
    {
        $payload = $this->request->getPost();
        if (! $this->validateData($payload, $rules)) return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        try { $action($this->validator->getValidated()); }
        catch (InvalidArgumentException $e) { return redirect()->back()->withInput()->with('errors', ['request' => $e->getMessage()]); }
        return redirect()->back()->with('message', $message);
    }
}

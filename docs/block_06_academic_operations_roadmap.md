# Block 6 Strategic Implementation Roadmap  
## Academic Operations Block  
### Staff Course Allocation and Student Course Registration  
### SaaS Multi-Tenant SHST Information Management System

---

## Document Status

**Block Number:** Block 6  
**Block Name:** Academic Operations  
**System Type:** SaaS multi-tenant information management system  
**Target Institutions:** Nigerian Schools of Health Sciences and Technology  
**Framework:** CodeIgniter 4  
**Authentication Foundation:** CodeIgniter Shield  
**Frontend Enhancement:** Vue.js Composition API where appropriate  
**Primary Users:** Tenant Super Admin, Tenant Admin, Registrar, HOD, Course Adviser, Lecturer, Exams Officer, Student  
**Dependency:** Block 1 and Block 5 must be stable. Block 4 payment eligibility services must exist or be stubbed consistently.  

---

# 1. Block 6 Purpose

Block 6 establishes the core academic operation layer that connects:

- Configured academic structure
- Active students
- Active staff
- Programmes
- Levels
- Semesters
- Courses
- Lecturers
- Course advisers
- Departmental approval
- Student academic participation

This block covers two major academic workflows:

1. **Staff Course Allocation**  
2. **Student Course Registration**

The purpose is to ensure that before any result processing begins, the system already knows:

- Which courses exist for each programme, level, and semester
- Which lecturer or teaching team is responsible for each course
- Which students officially registered each course
- Which course adviser reviewed the registration
- Which HOD or authorized officer approved it
- Which students are eligible or not eligible for examination and later result processing

This block prepares the clean academic participation data required by Block 7 Results.

---

# 2. Strategic Importance

Academic operations are the bridge between institutional records and result processing.

A result system cannot be trusted if course allocation and course registration are weak.

Block 6 prevents the following problems:

- Lecturers submitting scores for courses not assigned to them
- Students appearing in result sheets without course registration
- Students registering wrong courses
- Wrong course lists per level or programme
- Unclear carryover tracking
- Manual class lists conflicting with system records
- Course advisers approving registration outside their scope
- HODs approving courses outside their department
- Exam officers compiling results from unofficial class lists

Later blocks depend on Block 6 as follows:

| Later Block/Feature | Dependency on Block 6 |
|---|---|
| Result entry | Requires course allocation and registered class list |
| Result computation | Requires registered course units and student course registration |
| Carryover management | Requires failed courses and future registration linkage |
| Examination eligibility | Requires registration status and payment eligibility |
| Lecturer dashboard | Requires allocated courses |
| Student dashboard | Requires registered courses |
| Department reports | Requires allocation and registration summaries |
| Academic board reports | Requires approved course registration and class lists |

---

# 3. Block 6 Scope

## 3.1 In Scope

Block 6 includes:

1. Academic operation session selection  
2. Course allocation setup per tenant  
3. Course allocation by department/programme/level/semester  
4. Lecturer assignment to courses  
5. Team-teaching support  
6. Course adviser assignment  
7. Practical/lab/clinical supervisor assignment placeholder  
8. Course allocation review and approval  
9. Lecturer workload visibility  
10. Student course registration setup  
11. Course registration opening and closing  
12. Student course registration by programme, level, and semester  
13. Compulsory course registration  
14. Elective course registration  
15. Carryover course registration foundation  
16. Payment eligibility check integration  
17. Course adviser review  
18. HOD approval  
19. Add/drop foundation  
20. Registered class list generation  
21. Unregistered student reporting  
22. Course registration form generation  
23. Academic operation audit trail  
24. Academic operation reports  

---

## 3.2 Out of Scope

The following must not be implemented fully in Block 6:

- Result entry
- Result computation
- Result approval
- Result publication
- Transcript generation
- Final graduation decision
- Full examination timetable management
- Full exam attendance system
- Full clinical posting/logbook system
- Full professional council exam registration
- Full learning management system
- Staff payroll
- Student attendance tracking

However, Block 6 must create clean data structures that later modules can consume.

---

# 4. Non-Negotiable Rules for Block 6

1. Every academic operation record must be tenant-scoped.

2. A lecturer must not be allocated to a course outside the active tenant.

3. A course allocation must not link courses, staff, departments, programmes, levels, or semesters from different tenants.

4. A student must not register courses outside the student’s tenant.

5. A student must not register courses outside the student’s programme/level/allowed carryover scope unless an authorized override exists.

6. Course registration must respect tenant-configured rules.

7. Payment eligibility must be checked through the payment eligibility service, not duplicated manually.

8. Course allocation must exist before lecturer-specific result entry can happen in Block 7.

9. Course registration must exist before student results can be computed in Block 7.

10. Side navigation must be generated through the centralized navigation resolver.

11. All sensitive actions must be audited.

12. All UI must remain mobile-first.

13. Vue Composition API may be used for interactive allocation and registration screens.

14. No academic display data belonging to a tenant may be hard-coded.

---

# 5. Required Dependencies from Earlier Blocks

## 5.1 From Block 1

Block 6 depends on:

- Tenant resolver
- Tenant-scoped base model
- Tenant membership enforcement
- IAM groups
- Operational authority foundation
- Academic sessions
- Semesters
- Levels
- Departments
- Programmes
- Courses
- Tenant theme
- Department color codes
- Side navigation resolver
- Audit service
- API response standard
- Internal layouts

## 5.2 From Block 4

Block 6 depends on:

- Fee configuration
- Student payment status
- Payment eligibility service
- Payment control points
- Debtor status checking

If payment integration is not fully active during development, Block 6 must use a stubbed `PaymentEligibilityService` with the same contract that the final payment module will expose.

## 5.3 From Block 5

Block 6 depends on:

- Active student records
- Student programme placement
- Student level placement
- Student department placement
- Student academic status
- Active staff records
- Staff department placement
- Staff academic/non-academic category
- Lecturer identity
- Staff status

---

# 6. Block 6 Core Modules

---

## Module 1: Academic Operation Control Panel

### Purpose

To provide tenant administrators and academic officers with a single control area for selecting the academic context before managing course allocation or registration.

### Required Capabilities

Authorized users must be able to select:

- Academic session
- Semester
- Department
- Programme
- Level
- Operation type

Operation type includes:

- Course allocation
- Course registration
- Registration review
- Registration approval
- Class list generation

### Business Rules

- The active tenant must be resolved before loading any academic context.
- Only active academic sessions should be available for normal operations.
- Closed sessions should be read-only unless an authorized reopening workflow exists.
- Users should only see departments/programmes within their operational authority scope.
- HODs should see only their assigned department unless granted wider authority.
- Course advisers should see only assigned programme/level groups.

### Audit Requirements

Audit is not required for ordinary context selection.

Audit is required when:

- Academic operation period is opened
- Academic operation period is closed
- Course registration is reopened
- Course allocation is locked/unlocked

---

## Module 2: Course Allocation Settings

### Purpose

To allow each tenant to define how course allocation works in the institution.

### Required Capabilities

Tenant administrators must be able to configure:

- Whether course allocation is required before course registration
- Whether course allocation is required before result entry
- Who can allocate courses
- Whether HOD approval is required
- Whether lecturer acknowledgement is required
- Whether team teaching is allowed
- Whether lecturers can teach across departments
- Whether part-time lecturers can be allocated courses
- Whether allocation can be edited after approval
- Whether allocation should be locked after approval

### Configuration Scope

Course allocation rules may apply at:

- Tenant level
- Department level
- Programme level
- Academic session level
- Semester level

### Default Decision

The platform default shall be:

- Course allocation is required before result entry.
- HOD approval is required.
- Team teaching is allowed.
- Cross-department allocation is allowed only by authorized users.
- Approved allocation is locked unless reopened by authorized academic authority.

### Audit Requirements

Audit:

- Allocation settings created
- Allocation settings updated
- Allocation requirement changed
- Approval rule changed
- Locking rule changed

---

## Module 3: Staff Course Allocation

### Purpose

To assign lecturers, teaching teams, and supervisors to courses for a specific academic session and semester.

### Required Capabilities

Authorized users must be able to:

- View courses by department/programme/level/semester
- Assign primary lecturer to a course
- Assign supporting lecturer where team teaching is enabled
- Assign practical/lab/clinical supervisor placeholder where required
- Set lecturer responsibility type
- Add allocation notes
- Save allocation as draft
- Submit allocation for approval
- Approve allocation
- Reject allocation
- Return allocation for correction
- Lock approved allocation
- Generate course allocation report

### Lecturer Responsibility Types

The system must support:

- Primary Lecturer
- Supporting Lecturer
- Practical Supervisor
- Lab Supervisor
- Clinical/Field Supervisor
- Course Coordinator
- External Examiner placeholder

These labels must be configurable later where tenant-specific naming is required.

### Required Fields

A course allocation record must capture:

- Tenant ID
- Academic session ID
- Semester ID
- Department ID
- Programme ID
- Level ID
- Course ID
- Staff ID
- Responsibility type
- Allocation status
- Assigned by
- Approved by
- Approval date
- Lock status
- Notes
- Created by
- Updated by
- Created at
- Updated at

### Business Rules

- Staff must belong to the same tenant.
- Staff must be active.
- Staff must be academic or explicitly permitted for academic allocation.
- Course must belong to the same tenant.
- Course must be active.
- Department/programme/level/semester must belong to same tenant.
- One course may have multiple staff if team teaching is enabled.
- One course must have one primary lecturer unless the tenant explicitly allows no-primary allocation.
- A lecturer’s workload should be visible before final approval.
- Approved allocation must be locked from casual editing.
- Changes after approval must create a new audit record.

### Allocation Statuses

The system must support:

1. Draft
2. Submitted
3. Returned for correction
4. Approved
5. Rejected
6. Locked
7. Reopened
8. Archived

### Audit Requirements

Audit:

- Lecturer allocated to course
- Lecturer removed from course
- Allocation submitted
- Allocation approved
- Allocation rejected
- Allocation returned for correction
- Allocation locked
- Allocation reopened
- Allocation edited after approval

---

## Module 4: Lecturer Workload View

### Purpose

To allow authorized users to see lecturer teaching assignments and prevent unbalanced workload.

### Required Capabilities

The system must show:

- Lecturer name
- Department
- Assigned courses
- Course units
- Programme
- Level
- Semester
- Responsibility type
- Total assigned courses
- Total assigned units
- Allocation status

### User Visibility

- Platform admin may view only through controlled platform access.
- Tenant super admin may view all tenant workload records.
- Tenant admin may view assigned scope.
- HOD may view department workload.
- Lecturer may view own workload.

### Business Rule

Workload view is informational in Block 6.

It must not become payroll, attendance, or HR performance management.

### Audit Requirements

Ordinary viewing does not require audit unless viewed through platform-admin tenant access.

Audit is required for export.

---

## Module 5: Course Adviser Assignment

### Purpose

To assign course advisers to specific academic student groups for registration guidance and review.

### Required Capabilities

Authorized users must be able to assign course advisers by:

- Academic session
- Department
- Programme
- Level
- Semester, where applicable

The system must support:

- One course adviser per group
- Multiple course advisers per group where enabled
- Adviser replacement
- Adviser assignment history
- Adviser status

### Required Fields

Course adviser assignment must capture:

- Tenant ID
- Academic session ID
- Department ID
- Programme ID
- Level ID
- Semester ID where applicable
- Staff ID
- Assignment status
- Assigned by
- Assigned at
- Start date
- End date where applicable

### Business Rules

- Adviser must be active staff in the same tenant.
- Adviser must have appropriate staff status or authority.
- A student group should have at least one adviser before course registration review begins.
- Course adviser visibility must be scope-bound.

### Audit Requirements

Audit:

- Course adviser assigned
- Course adviser changed
- Course adviser removed
- Course adviser assignment deactivated

---

## Module 6: Course Registration Settings

### Purpose

To allow each tenant to define how students register courses.

### Required Capabilities

Tenant administrators must configure:

- Whether course registration is semester-based or session-based
- Registration opening date
- Registration closing date
- Late registration policy
- Add/drop policy
- Payment requirement before registration
- Minimum payment required before registration
- Whether debtor students can register
- Whether adviser review is required
- Whether HOD approval is required
- Whether registration auto-approves after adviser review
- Whether students can self-register
- Whether admin-assisted registration is allowed
- Maximum course load
- Minimum course load
- Carryover registration rule
- Elective selection rule

### Configuration Scope

Rules may apply at:

- Tenant level
- Programme level
- Department level
- Level level
- Session level
- Semester level

### Default Decision

The platform default shall be:

- Course registration is semester-based.
- Payment eligibility is required before final registration.
- Course adviser review is required.
- HOD approval is required.
- Approved registration is locked unless reopened by authorized authority.
- Add/drop requires approval.

### Audit Requirements

Audit:

- Course registration settings created
- Course registration settings updated
- Registration opened
- Registration closed
- Registration reopened
- Payment rule changed
- Approval rule changed

---

## Module 7: Student Course Registration

### Purpose

To allow students to register the courses they are officially taking for a session/semester.

### Required Capabilities

The system must allow:

- Student self-registration where enabled
- Admin-assisted registration where authorized
- Display of eligible courses
- Automatic inclusion of compulsory courses where configured
- Elective selection where applicable
- Carryover course inclusion where applicable
- Payment eligibility check
- Course load calculation
- Draft registration save
- Submission for review
- Course adviser review
- HOD approval
- Final registration locking
- Course registration form generation

### Eligible Course Sources

Eligible courses may come from:

- Student programme
- Student level
- Active semester
- Academic session
- Carryover history from previous results, when Block 7 becomes available
- Manually approved carryover/imported academic record where needed

### Required Fields

Course registration record must capture:

- Tenant ID
- Student ID
- Academic session ID
- Semester ID
- Department ID
- Programme ID
- Level ID
- Registration status
- Registration type
- Submitted by
- Submitted at
- Reviewed by
- Reviewed at
- Approved by
- Approved at
- Lock status
- Payment eligibility status at time of submission
- Created by
- Updated by

Course registration item must capture:

- Tenant ID
- Registration ID
- Course ID
- Course code snapshot
- Course title snapshot
- Course unit snapshot
- Course category
- Course source: normal/elective/carryover/add-drop/admin-added
- Status

### Why Course Snapshot Is Required

Course code, title, unit, and category must be stored as snapshots inside registration items.

This prevents old registrations from changing historically when a tenant later edits a course title or course unit.

### Registration Statuses

The system must support:

1. Draft
2. Submitted
3. Under adviser review
4. Returned for correction
5. Adviser approved
6. HOD approved
7. Rejected
8. Locked
9. Reopened
10. Cancelled

### Business Rules

- Student must belong to active tenant.
- Student must be active or otherwise eligible by configured policy.
- Student must belong to the programme and level being registered.
- Registration must be within open registration period unless authorized override exists.
- Payment eligibility must be checked where configured.
- Course load must obey configured minimum/maximum rules.
- Compulsory courses must not be removed unless authorized.
- Elective choices must obey tenant/programme rules.
- Carryover courses must be clearly marked.
- Approved registration must be locked from student edits.
- Changes after approval must use add/drop or authorized correction workflow.

### Audit Requirements

Audit:

- Registration draft created
- Registration submitted
- Course added
- Course removed
- Registration returned for correction
- Adviser approved registration
- HOD approved registration
- Registration rejected
- Registration locked
- Registration reopened
- Registration cancelled
- Admin-assisted registration performed

---

## Module 8: Add/Drop Course Foundation

### Purpose

To support controlled course changes after initial registration.

### Required Capabilities

The system must support:

- Add course request
- Drop course request
- Reason for request
- Adviser review
- HOD approval
- Updated registration record
- Add/drop history

### Required Fields

Add/drop request must capture:

- Tenant ID
- Student ID
- Registration ID
- Request type: add/drop
- Course ID
- Reason
- Status
- Requested by
- Reviewed by
- Approved by
- Created at
- Updated at

### Business Rules

- Add/drop must respect configured deadline.
- Add/drop must not break course load rules unless authorized.
- Dropping compulsory courses must require higher approval.
- Approved add/drop must update the registration item status, not delete history.

### Statuses

- Draft
- Submitted
- Under review
- Approved
- Rejected
- Cancelled

### Audit Requirements

Audit:

- Add/drop requested
- Add/drop approved
- Add/drop rejected
- Add/drop cancelled
- Registration changed through add/drop

---

## Module 9: Class List Generation

### Purpose

To generate official course-based student lists for lecturers, departments, exams office, and later result processing.

### Required Capabilities

The system must generate:

- Course class list
- Programme-level registration list
- Departmental registration list
- Lecturer class list
- Unregistered student list
- Pending registration list
- Approved registration list
- Rejected/returned registration list

### Class List Must Include

- Tenant name
- Academic session
- Semester
- Department
- Programme
- Level
- Course code
- Course title
- Lecturer name where allocated
- Student number
- Student name
- Student status
- Registration status
- Date generated
- Generated by

### Business Rule

Only approved or specifically allowed registration statuses should appear in official class lists.

Draft registration must not appear in official lecturer result-entry lists.

### Audit Requirements

Audit report exports.

Ordinary viewing may not require audit unless sensitive access context applies.

---

# 7. User Roles and Operational Authorities

## IAM Groups Involved

Block 6 uses these IAM groups:

1. Platform Admin
2. Tenant Super Admin
3. Tenant Admin
4. Lecturer
5. Student

## Operational Authorities Involved

Block 6 uses these operational authorities:

- HOD
- Course Adviser
- Exams Officer
- Registrar
- Academic Officer
- Departmental Officer
- Course Allocation Officer
- Course Registration Officer
- Result Preparation Officer placeholder

## Access Control Matrix

| User/Authority | Course Allocation | Course Registration | Approval | Reports |
|---|---|---|---|---|
| Platform Admin | Controlled support access only | Controlled support access only | No normal approval | Platform/tenant support reports |
| Tenant Super Admin | Full tenant scope | Full tenant scope | Where configured | All tenant reports |
| Tenant Admin | Assigned scope | Assigned scope | Based on authority | Assigned reports |
| HOD | Department scope | Department scope | Department approval | Department reports |
| Course Adviser | Assigned programme/level scope | Review assigned students | Adviser review only | Assigned group reports |
| Lecturer | View allocated courses | View registered class lists for allocated courses | No normal registration approval | Own course lists |
| Student | No allocation access | Own registration only | No approval | Own registration form |

---

# 8. Required Services

Block 6 must include or extend the following services:

1. `AcademicOperationContextService`
2. `CourseAllocationSettingsService`
3. `CourseAllocationService`
4. `LecturerWorkloadService`
5. `CourseAdviserAssignmentService`
6. `CourseRegistrationSettingsService`
7. `CourseEligibilityService`
8. `CourseRegistrationService`
9. `CourseRegistrationApprovalService`
10. `AddDropCourseService`
11. `ClassListService`
12. `PaymentEligibilityService` integration
13. `OperationalAuthorityService` integration
14. `AuditService` integration
15. `NotificationService` integration
16. `ReportExportService` integration

## Service Layer Rule

Controllers must not contain allocation or registration business logic.

Controllers must:

- Receive request
- Validate request
- Resolve tenant context
- Check authorization
- Call service
- Return view or standard API response

---

# 9. Required Controllers

## Tenant Admin Controllers

- `CourseAllocationController`
- `CourseAllocationSettingsController`
- `CourseAdviserAssignmentController`
- `CourseRegistrationSettingsController`
- `CourseRegistrationReviewController`
- `AcademicOperationReportController`

## Lecturer Controllers

- `LecturerCourseDashboardController`
- `LecturerClassListController`

## Student Controllers

- `StudentCourseRegistrationController`
- `StudentRegisteredCoursesController`
- `StudentAddDropController`

## API Controllers

- `CourseAllocationApiController`
- `CourseRegistrationApiController`
- `CourseEligibilityApiController`
- `ClassListApiController`
- `LecturerWorkloadApiController`

---

# 10. Required Models or Data Areas

The implementation must include tenant-scoped models for:

1. Course allocation settings
2. Course allocations
3. Course allocation staff items, where multiple lecturers exist
4. Course adviser assignments
5. Course registration settings
6. Course registration periods
7. Course registrations
8. Course registration items
9. Add/drop requests
10. Add/drop request items where needed
11. Registration approval history
12. Allocation approval history
13. Registration lock records where needed
14. Class list export log where needed

All tenant-owned models must extend the tenant-scoped model pattern established in Block 1.

---

# 11. Required Navigation Items

Navigation must be resolved centrally through the navigation resolver.

## Tenant Admin Navigation

Academic Operations:

- Academic Operations Dashboard
- Course Allocation
- Lecturer Workload
- Course Advisers
- Course Registration Settings
- Registration Review
- Registration Approvals
- Class Lists
- Academic Operation Reports

## Lecturer Navigation

My Academic Work:

- My Courses
- My Class Lists
- My Course Students

## Student Navigation

My Academics:

- Course Registration
- Registered Courses
- Add/Drop Request
- Registration Form

## Visibility Rules

- Students must not see admin course allocation pages.
- Lecturers must not see tenant course registration settings unless given authority.
- HODs must only see department-scoped operations unless granted wider scope.
- Course advisers must only see assigned registration groups.

---

# 12. UI and Layout Requirements

## General UI Rules

All Block 6 screens must use the layout foundation from Block 1.

Each internal page must support:

- Left side navigation
- Right main content area
- Top/middle/bottom content sections
- Mobile sidebar toggle
- Tenant theme
- Department color accents where context is department-specific

## Mobile-First Rules

Because most users will use mobile phones, the following are mandatory:

- Registration forms must be step-based or sectioned.
- Course lists must become cards on small screens.
- Course selection must be touch-friendly.
- Approval actions must be clearly separated to prevent accidental approval.
- Course adviser review must show student summary before action.
- Lecturer class lists must be searchable and paginated.
- Large tables must collapse into mobile-friendly cards.
- Bulk actions must require confirmation.

## Department Color Usage

Department color should appear in:

- Department header cards
- Programme labels
- Student registration context
- Course list badges
- Lecturer class list context
- Approval page accents

Department color must not overpower readability.

---

# 13. Vue Composition API Usage

Vue Composition API should be used for interactive academic operation screens.

## Vue Should Be Used For

- Course allocation grids
- Lecturer search and assignment
- Lecturer workload preview
- Dependent dropdowns: department → programme → level → semester → courses
- Course registration wizard
- Course load calculation
- Payment eligibility status refresh
- Elective course selection
- Carryover course display
- Add/drop request form
- Adviser review dashboard filters
- Class list filters

## Full Page Reload Should Be Used For

- Final course allocation submission
- Final course allocation approval
- Final course registration submission
- Final adviser approval
- Final HOD approval
- Registration lock/unlock
- Add/drop final approval
- Sensitive export generation where required

## API Response Rule

All API responses must use the standard response shape:

```json
{
  "success": true,
  "message": "Operation completed successfully.",
  "data": {},
  "errors": {},
  "meta": {}
}
```

Sensitive actions should include:

```json
{
  "audit_reference": "AUD-2026-000001"
}
```

---

# 14. Validation Rules

## Course Allocation Validation

- Tenant context is required.
- Academic session is required.
- Semester is required.
- Course is required.
- Course must belong to tenant.
- Staff is required.
- Staff must belong to tenant.
- Staff must be active.
- Staff must be allocatable.
- Department/programme/level must belong to tenant.
- Duplicate primary lecturer allocation must be prevented unless allowed.
- Approved allocation cannot be edited without reopening.

## Course Adviser Validation

- Tenant context is required.
- Staff must belong to tenant.
- Staff must be active.
- Programme must belong to tenant.
- Level must belong to tenant.
- Assignment must not conflict unless multiple advisers are allowed.

## Course Registration Validation

- Tenant context is required.
- Student must belong to tenant.
- Student must be active or eligible.
- Student must have programme and level placement.
- Academic session must be valid.
- Semester must be valid.
- Registration period must be open unless override exists.
- Payment eligibility must pass where configured.
- Courses must belong to the student’s programme/level/semester or valid carryover scope.
- Minimum/maximum course load must be respected.
- Duplicate course registration must be prevented.
- Approved registration cannot be edited directly.

## Add/Drop Validation

- Registration must exist.
- Registration must belong to tenant.
- Add/drop period must be open unless override exists.
- Course must be valid.
- Request reason is required.
- Request must not duplicate pending request.
- Approval must respect authority scope.

---

# 15. Audit Requirements

Block 6 must audit the following events:

## Course Allocation Audit

- Allocation settings updated
- Lecturer assigned to course
- Lecturer removed from course
- Course allocation submitted
- Course allocation approved
- Course allocation rejected
- Course allocation returned for correction
- Course allocation locked
- Course allocation reopened
- Course allocation edited after approval

## Course Adviser Audit

- Course adviser assigned
- Course adviser changed
- Course adviser removed
- Course adviser assignment deactivated

## Course Registration Audit

- Registration settings updated
- Registration period opened
- Registration period closed
- Registration period reopened
- Student registration draft created
- Course added to registration
- Course removed from registration
- Registration submitted
- Registration returned for correction
- Registration adviser-approved
- Registration HOD-approved
- Registration rejected
- Registration locked
- Registration reopened
- Registration cancelled
- Admin-assisted registration performed

## Add/Drop Audit

- Add/drop requested
- Add/drop approved
- Add/drop rejected
- Add/drop cancelled
- Registration changed through add/drop

## Export Audit

- Class list exported
- Registration report exported
- Lecturer workload exported

---

# 16. Notification Requirements

Block 6 should trigger notifications for key workflow events.

## Course Allocation Notifications

Notify lecturer when:

- Course is allocated to lecturer
- Course allocation is changed
- Course allocation is approved
- Course allocation is withdrawn

Notify HOD or academic officer when:

- Course allocation is submitted for approval
- Course allocation requires correction

## Course Registration Notifications

Notify student when:

- Course registration opens
- Registration is submitted successfully
- Registration is returned for correction
- Registration is approved
- Registration is rejected
- Add/drop request is approved or rejected

Notify course adviser when:

- Student submits registration for review
- Registration requires adviser action

Notify HOD when:

- Registration batch is ready for approval

## Channel Rule

In-app notification is required.

Email/SMS may be integrated later but the notification events must be recorded from this block.

---

# 17. Reports Required in Block 6

## Course Allocation Reports

- Department course allocation report
- Lecturer workload report
- Unallocated courses report
- Allocated courses by programme
- Allocated courses by semester
- Team-taught courses report
- Course adviser assignment report

## Course Registration Reports

- Registered students list
- Unregistered students list
- Pending registration list
- Adviser-approved registration list
- HOD-approved registration list
- Rejected registration list
- Course-based class list
- Programme registration summary
- Department registration summary
- Carryover registration report foundation
- Add/drop request report

## Export Requirements

All exported reports must include:

- Tenant name
- Report title
- Academic session
- Semester
- Department/programme/level filters
- Date generated
- Generated by
- Applied filters

---

# 18. Dashboards Required in Block 6

## Tenant Academic Operations Dashboard

Must show:

- Active academic session
- Active semester
- Number of allocated courses
- Number of unallocated courses
- Number of active course advisers
- Registration status summary
- Registered students count
- Unregistered students count
- Pending adviser reviews
- Pending HOD approvals

## HOD Dashboard Additions

Must show:

- Department courses
- Department course allocations
- Department lecturer workload
- Pending registration approvals
- Department registration summary

## Course Adviser Dashboard Additions

Must show:

- Assigned programme/level groups
- Submitted registrations awaiting review
- Returned registrations
- Approved registrations

## Lecturer Dashboard Additions

Must show:

- Allocated courses
- Course class lists
- Academic session/semester context
- Course allocation status

## Student Dashboard Additions

Must show:

- Course registration status
- Registration period status
- Registered courses summary
- Payment eligibility status where configured
- Adviser/HOD approval status
- Registration form download/view action

---

# 19. Implementation Order for Block 6

Developers must implement Block 6 in this order:

## Step 1: Academic Operation Context

- Build academic context selector
- Enforce tenant scope
- Enforce session/semester status
- Enforce department/programme/level scope

## Step 2: Course Allocation Settings

- Build allocation settings
- Define default allocation policy
- Connect settings to tenant/programme/department where needed
- Audit configuration changes

## Step 3: Staff Course Allocation

- Build allocation records
- Build lecturer assignment flow
- Support team teaching
- Support draft/submitted/approved/locked statuses
- Enforce staff/course tenant matching
- Add lecturer workload preview

## Step 4: Course Adviser Assignment

- Build adviser assignment
- Scope adviser to programme/level/semester
- Add adviser visibility rules
- Add adviser dashboard placeholders

## Step 5: Course Registration Settings

- Build registration settings
- Build registration period management
- Integrate payment eligibility service contract
- Configure approval rules

## Step 6: Student Course Registration

- Build student course registration wizard
- Load eligible courses
- Handle compulsory/elective/carryover placeholders
- Calculate course load
- Save draft
- Submit registration

## Step 7: Registration Review and Approval

- Build course adviser review
- Build HOD approval
- Support return for correction
- Support rejection
- Lock approved registration

## Step 8: Add/Drop Foundation

- Build add/drop request
- Build add/drop review and approval
- Maintain history without deleting original registration items

## Step 9: Class Lists and Reports

- Generate course class lists
- Generate registration reports
- Generate allocation reports
- Add export logging

## Step 10: Final Stabilization

- Tenant isolation testing
- Authorization testing
- Payment eligibility testing
- Mobile testing
- Audit testing
- Report testing
- Result readiness testing

---

# 20. Testing Checklist

## Tenant Isolation Tests

- Tenant A cannot allocate Tenant B courses.
- Tenant A cannot assign Tenant B staff.
- Tenant A cannot register Tenant B students.
- Tenant A cannot view Tenant B registration records.
- Tenant A cannot export Tenant B class lists.
- URL ID manipulation does not expose another tenant’s academic records.

## Course Allocation Tests

- Active staff can be allocated to valid course.
- Inactive staff cannot be allocated.
- Non-tenant staff cannot be allocated.
- Course outside tenant cannot be allocated.
- Duplicate primary lecturer rule works.
- Team teaching rule works.
- Approved allocation locks correctly.
- Reopened allocation creates audit record.

## Course Adviser Tests

- Adviser can be assigned to programme/level.
- Adviser sees only assigned student groups.
- Adviser cannot review outside scope.
- Adviser replacement preserves history.

## Course Registration Tests

- Student can view eligible courses.
- Student cannot register outside programme/level.
- Student cannot register when registration is closed.
- Payment ineligibility blocks registration where configured.
- Course load rule works.
- Compulsory course rule works.
- Elective rule works.
- Draft registration does not appear in official class list.
- Approved registration locks correctly.

## Approval Tests

- Course adviser can approve assigned registrations.
- Course adviser cannot approve outside assigned scope.
- HOD can approve department registrations.
- HOD cannot approve outside department unless authorized.
- Returned registration can be corrected and resubmitted.
- Rejected registration is not treated as official registration.

## Add/Drop Tests

- Student can request add/drop within allowed period.
- Request outside period is blocked unless authorized.
- Add/drop approval updates registration history.
- Dropped course is not deleted from history.

## Mobile Tests

- Student registration wizard works on mobile.
- Course selection is touch-friendly.
- Adviser review is readable on mobile.
- Class lists do not overflow badly.
- Approval buttons require clear confirmation.

## Audit Tests

- Course allocation events are audited.
- Registration submission is audited.
- Registration approval is audited.
- Add/drop actions are audited.
- Report export is audited.

---

# 21. Acceptance Criteria

Block 6 is complete only when:

1. Tenant admins can configure course allocation rules.

2. Authorized users can allocate lecturers to courses.

3. Course allocation supports draft, submission, approval, rejection, locking, and reopening.

4. Lecturer workload can be viewed by authorized users.

5. Course advisers can be assigned to programme/level groups.

6. Tenant admins can configure course registration rules.

7. Course registration periods can be opened, closed, and reopened by authorized users.

8. Students can register eligible courses.

9. Course registration respects payment eligibility where configured.

10. Course registration respects programme, level, semester, course category, and course load rules.

11. Course adviser review works.

12. HOD approval works.

13. Approved registrations are locked.

14. Add/drop foundation works with approval and history.

15. Official class lists can be generated.

16. Unregistered and pending registration reports can be generated.

17. Lecturer dashboards show allocated courses.

18. Student dashboards show course registration status.

19. Tenant isolation is enforced throughout.

20. All sensitive academic operation actions are audited.

21. The block produces clean data that Block 7 Results can safely consume.

---

# 22. Result Readiness Contract

Block 6 must expose clean data for Block 7.

Before Block 7 begins, the system must be able to answer:

1. Which courses were offered in a session/semester?
2. Which lecturer was responsible for each course?
3. Which students registered each course?
4. Which registrations were approved?
5. Which students were unregistered?
6. Which students had pending registrations?
7. Which course registrations were locked?
8. Which courses are valid for result entry?
9. Which lecturer is allowed to submit scores for each course?
10. Which students should appear on a course result sheet?

Block 7 must not independently recreate course allocation or course registration logic.

It must consume the approved records produced by Block 6.

# Block 7 Strategic Implementation Roadmap  
## Student Result Compilation, Computation, Approval, and Publication  
### SaaS Multi-Tenant SHST Information Management System

---

## Document Status

**Block:** Block 7  
**Block Name:** Results  
**Project Type:** SaaS multi-tenant SHST information management system  
**Framework:** CodeIgniter 4  
**Authentication Foundation:** CodeIgniter Shield  
**Frontend Enhancement:** Vue.js Composition API  
**Primary Audience:** Developers, technical leads, AI coding agents  
**Dependency Level:** Depends strictly on Blocks 1–6  
**Document Purpose:** Provide a final implementation roadmap for result processing without reopening foundation-level architectural decisions.

---

# 1. Block 7 Purpose

Block 7 implements the controlled result processing system for the SaaS SHST platform.

This block allows each tenant institution to configure result rules, enter CA and examination scores, compute totals and grades, process carryovers, review results through approved institutional stages, publish results to students, and maintain a complete audit history of all result-related actions.

This block must be handled with the highest discipline because result records are academically sensitive and may affect:

- Student progression
- Carryover status
- Repeat status
- Graduation eligibility
- Professional council eligibility
- Institutional credibility
- Academic Board decisions
- Student disputes
- Legal or administrative complaints

---

# 2. Block 7 Strategic Position

Block 7 must not operate independently.

It depends on:

| Dependency | Source Block | Why It Matters |
|---|---|---|
| Tenant isolation | Block 1 | Results must never leak across schools |
| Academic sessions | Block 1 | Every result belongs to a session |
| Semesters | Block 1 | Results are processed by semester or session |
| Levels | Block 1 | Results depend on student level |
| Departments | Block 1 | Results are reviewed departmentally |
| Programmes | Block 1 | Grading and progression may vary by programme |
| Courses | Block 1 | Scores are entered against valid courses |
| Roles and authorities | Block 1 | Result access depends on IAM group and operational authority |
| Audit trail | Block 1 | Every sensitive result action must be logged |
| Payment control | Block 4 | Result viewing may depend on payment status |
| Student records | Block 5 | Results belong to official student records |
| Staff records | Block 5 | Score entry belongs to authorized staff |
| Course allocation | Block 6 | Only assigned lecturers can submit course results |
| Course registration | Block 6 | Only registered courses should receive student scores |

---

# 3. Block 7 Core Decision

Result processing shall be built as a tenant-configurable, approval-driven, audit-protected academic workflow.

The system must not assume one universal grading system, CA/exam ratio, result format, or approval process.

Each tenant must configure result rules according to its academic policy, while the platform enforces standard safety, audit, and tenant-isolation controls.

---

# 4. Non-Negotiable Rules for Block 7

1. No result data shall exist without `tenant_id`.

2. No result shall be entered for a student outside the active tenant.

3. No result shall be entered for a course outside the active tenant.

4. No result shall be entered for a student who did not register the course, unless a controlled exception workflow is used.

5. No lecturer shall enter scores for a course not allocated to them.

6. No result shall be published without passing the configured approval workflow.

7. No published result shall be edited directly.

8. Result corrections must create correction records and audit entries.

9. All result actions must be tenant-scoped.

10. All sensitive result actions must be audited.

11. Result viewing by students must respect tenant payment rules where applicable.

12. Result rules must be configurable per tenant and, where necessary, per programme or course.

13. The system must support missing scores, withheld results, carryovers, special exams, resits, and result corrections.

14. Mobile-first result views must be supported.

15. Vue Composition API may be used for score entry, validation, filtering, and review interactions.

16. Full page reload shall be used for final publication, locking, major approvals, and sensitive result actions where server confirmation is required.

---

# 5. Block 7 Scope

## 5.1 In Scope

Block 7 includes:

1. Result rule configuration
2. Grading scale configuration
3. CA/exam ratio configuration
4. Result batch creation
5. Course score sheet generation
6. CA score entry
7. Examination score entry
8. Total score computation
9. Grade computation
10. Grade point computation where applicable
11. Remark computation
12. Missing score tracking
13. Failed course detection
14. Carryover detection
15. Resit/special exam support
16. Withheld result support
17. Result approval workflow
18. Departmental result review
19. Exams office review
20. Academic authority approval
21. Result publication
22. Student result viewing
23. Result correction workflow
24. Result locking
25. Result reports
26. Result audit logs

---

## 5.2 Out of Scope

The following must not be implemented as part of Block 7:

- Full transcript generation
- Certificate processing
- Graduation clearance
- Convocation module
- Alumni module
- Professional council portal integration
- External examiner portal
- Historical result migration at scale
- Biometric exam attendance
- Examination timetable generation
- CBT integration
- Library or hostel clearance
- Advanced analytics dashboard beyond basic result reports

These can be handled in later enhancement phases.

---

# 6. Main Modules Inside Block 7

---

## Module 1: Result Rule Configuration

### Purpose

To allow each tenant to define how results are calculated, graded, reviewed, and published.

### Required Capabilities

Tenant super admin, exams officer, or authorized academic administrator must be able to configure:

- Grading scale
- Pass mark
- CA/exam ratio
- Maximum CA score
- Maximum examination score
- Maximum total score
- Grade point values where applicable
- Remarks
- Failed-course rule
- Carryover rule
- Resit rule
- Special exam rule
- Withheld result rule
- Result publication rule
- Result correction rule
- Result locking rule
- Approval workflow

### Rule Scope

Result rules may be scoped by:

- Tenant-wide
- Department
- Programme
- Level
- Semester
- Course
- Course category

### Business Rule

Where a lower-level rule exists, it overrides the broader rule.

Recommended rule priority:

1. Course-level rule
2. Programme-level rule
3. Department-level rule
4. Tenant-wide rule
5. Platform default fallback

### Example Configurable Rules

A tenant may define:

| Score Range | Grade | Grade Point | Remark |
|---|---|---|---|
| 70–100 | A | 5.00 | Excellent |
| 60–69 | B | 4.00 | Very Good |
| 50–59 | C | 3.00 | Good |
| 45–49 | D | 2.00 | Pass |
| 0–44 | F | 0.00 | Fail |

Another tenant may use a different scale.

The platform must not hard-code one grading model.

### Audit Requirements

Audit:

- Result rule created
- Result rule updated
- Grading scale changed
- Pass mark changed
- CA/exam ratio changed
- Approval workflow changed
- Result rule activated/deactivated

---

## Module 2: Result Batch Management

### Purpose

To group result processing by tenant, session, semester, department, programme, level, and course.

### Required Capabilities

Authorized users must be able to:

- Create result batch
- Select academic session
- Select semester
- Select department
- Select programme
- Select level
- Generate course score sheets
- Track batch status
- Submit batch for review
- Lock batch after publication

### Batch Statuses

The system must support:

1. Draft
2. Open for score entry
3. Submitted by lecturer
4. Under departmental review
5. Returned for correction
6. Reviewed by HOD
7. Under exams office review
8. Approved
9. Published
10. Locked
11. Archived

### Business Rule

A result batch must not be created unless:

- Tenant exists and is active
- Academic session exists
- Semester exists
- Programme exists
- Level exists
- Courses exist
- Course allocation exists
- Students have registered courses

### Audit Requirements

Audit:

- Result batch created
- Batch opened
- Batch submitted
- Batch returned
- Batch approved
- Batch published
- Batch locked
- Batch archived

---

## Module 3: Course Score Sheet Generation

### Purpose

To generate score sheets based on course registration and staff course allocation.

### Required Capabilities

The system must generate a course score sheet containing:

- Tenant
- Academic session
- Semester
- Department
- Programme
- Level
- Course
- Assigned lecturer
- Registered students
- CA score field
- Exam score field
- Total score
- Grade
- Remark
- Status

### Business Rules

A student appears on a course score sheet only if:

- Student belongs to the active tenant
- Student is active or academically eligible
- Student registered the course
- Course registration was approved
- Course belongs to the same programme/level/semester context

A lecturer can access a course score sheet only if:

- Lecturer belongs to the tenant
- Lecturer has active staff profile
- Lecturer is assigned to the course
- Score entry window is open
- Result batch is not locked

### Exception Handling

The system must support controlled exceptions such as:

- Student registered late
- Student approved for special exam
- Student approved for resit
- Student omitted due to administrative correction
- Student result withheld

Every exception must be authorized and audited.

### Audit Requirements

Audit:

- Score sheet generated
- Student added by exception
- Student removed by exception
- Score sheet regenerated
- Lecturer accessed score sheet

---

## Module 4: Score Entry

### Purpose

To allow authorized lecturers or approved academic officers to enter CA and examination scores.

### Required Capabilities

The score entry interface must support:

- CA score entry
- Examination score entry
- Auto-computation of total
- Auto-computation of grade
- Auto-computation of remark
- Missing score flag
- Absent flag
- Withheld flag
- Save draft
- Submit final score sheet
- Bulk entry where appropriate
- Mobile-friendly entry mode
- Desktop table entry mode

### Vue Usage

Vue Composition API should be used for:

- Inline score validation
- Auto total computation preview
- Auto grade preview
- Bulk score entry
- Student search/filter
- Save draft interactions
- Missing score warnings
- Submission readiness checks

### Full Reload Usage

Full page reload should be used for:

- Final score sheet submission
- Returning submitted result for correction
- Approving result
- Publishing result
- Locking result

### Validation Rules

The system must validate:

- CA score is numeric
- Exam score is numeric
- CA score does not exceed configured maximum
- Exam score does not exceed configured maximum
- Total score does not exceed configured maximum
- Required scores are present unless marked absent, missing, withheld, special, or resit
- Student belongs to tenant
- Course belongs to tenant
- Lecturer is authorized
- Batch is open
- Result is not locked

### Business Rule

Draft scores may be edited by the authorized lecturer while the batch is open.

Submitted scores cannot be edited unless returned for correction.

Published scores cannot be edited directly.

### Audit Requirements

Audit:

- Score draft saved
- Score changed
- Score submitted
- Missing score marked
- Absent marked
- Withheld marked
- Score sheet submitted
- Score entry attempt denied

---

## Module 5: Result Computation Engine

### Purpose

To calculate totals, grades, grade points, remarks, pass/fail status, and carryover status based on configured rules.

### Required Capabilities

The computation engine must support:

- Total score calculation
- Grade determination
- Grade point determination
- Pass/fail decision
- Carryover decision
- Remark generation
- GPA/CGPA placeholder where applicable
- Programme-specific grading
- Course-specific grading
- Resit computation
- Special exam computation
- Withheld result exclusion
- Missing score detection

### Business Rule

The computation engine must not be hard-coded into controllers or views.

It must exist as a service that can be tested independently.

### Required Service

`ResultComputationService`

This service must accept:

- Tenant ID
- Result rule context
- Student ID
- Course ID
- CA score
- Exam score
- Result status flags

It must return:

- Total score
- Grade
- Grade point
- Remark
- Pass/fail status
- Carryover status
- Computation metadata
- Error/warning list

### Edge Cases

The computation engine must handle:

- CA present, exam missing
- Exam present, CA missing
- Student absent from exam
- Student score withheld
- Student approved for special exam
- Student approved for resit
- Course has practical component
- Course has different pass mark
- Programme has different grading scale
- Total score falls exactly on boundary
- Score exceeds maximum allowed value

### Audit Requirements

Audit computation only when computation result is saved, submitted, approved, corrected, or published.

Do not audit every frontend preview computation unless it changes stored data.

---

## Module 6: Result Review and Approval Workflow

### Purpose

To enforce institutional result approval before publication.

### Required Capabilities

The system must support configurable approval stages such as:

1. Lecturer submission
2. HOD review
3. Departmental review
4. Exams officer review
5. Academic authority approval
6. Final publication

### Approval Actions

Authorized users must be able to:

- Review score sheet
- Approve result
- Return for correction
- Reject result
- Add review comment
- Escalate to next approval stage
- View approval history
- Lock approved result

### Approval Authority

Approval access must depend on:

- IAM group
- Operational authority
- Tenant membership
- Department scope
- Programme scope
- Workflow stage
- Batch status

### Business Rule

A user cannot approve a result at a stage they are not responsible for.

A user cannot approve their own stage where separation of duty is configured.

A result cannot skip required approval stages unless tenant policy explicitly permits it.

### Audit Requirements

Audit:

- Result reviewed
- Result approved
- Result returned for correction
- Result rejected
- Approval comment added
- Approval stage changed
- Unauthorized approval attempt

---

## Module 7: Result Publication

### Purpose

To release approved results to students and authorized staff.

### Required Capabilities

Authorized academic officers must be able to:

- Publish result by batch
- Publish result by course
- Publish result by student group
- Publish result by programme
- Withhold selected results
- Unpublish where policy allows
- Lock after publication
- Notify students

### Publication Rules

Result publication must check:

- Result batch is approved
- Required approval stages are complete
- Result is not already locked
- Student is eligible to view result
- Tenant payment rule for result viewing is satisfied where configured

### Student Result Viewing

Students should be able to view:

- Academic session
- Semester
- Course code
- Course title
- Score or grade, depending on tenant policy
- Total score, where allowed
- Grade
- Remark
- Carryover status
- Publication date

### Tenant Configurable Display

Tenants should configure whether students see:

- Scores and grades
- Grades only
- Remarks only
- GPA/CGPA where applicable
- Carryover status
- Withheld notification

### Audit Requirements

Audit:

- Result published
- Result unpublished
- Result locked
- Student viewed result
- Result withheld from student
- Publication denied due to payment or eligibility rule

---

## Module 8: Result Withholding

### Purpose

To allow controlled withholding of results for academic, disciplinary, financial, or administrative reasons.

### Required Capabilities

Authorized users must be able to:

- Withhold individual student result
- Withhold course result
- Withhold semester result
- Add withholding reason
- Release withheld result
- Track withholding history

### Supported Reasons

The system must support reasons such as:

- Outstanding fees
- Examination malpractice investigation
- Missing clearance
- Missing CA
- Missing exam score
- Administrative review
- Disciplinary case
- Academic Board decision
- Other tenant-defined reason

### Business Rule

Withholding a result must not delete the result.

It only controls visibility and publication status.

### Audit Requirements

Audit:

- Result withheld
- Withholding reason added
- Withheld result released
- Withholding updated
- Unauthorized withholding attempt

---

## Module 9: Result Correction Workflow

### Purpose

To correct errors without destroying audit history.

### Required Capabilities

The system must support:

- Correction request
- Correction reason
- Old score preservation
- New score proposal
- Correction approval
- Correction rejection
- Corrected result publication
- Correction audit trail

### Correction Triggers

Correction may be needed due to:

- Wrong CA score
- Wrong examination score
- Missing score found
- Wrong student included
- Wrong course mapping
- Computation error
- Administrative error
- Approved petition

### Business Rule

Published result must not be edited directly.

Correction must follow:

1. Correction request
2. Review
3. Approval
4. Corrected value saved
5. Result recomputed
6. Audit trail recorded
7. Student view updated if applicable

### Audit Requirements

Audit:

- Correction requested
- Correction approved
- Correction rejected
- Score corrected
- Grade recomputed
- Corrected result republished

---

# 7. Required Data Ownership Rules

## Tenant-Owned Data

All Block 7 tables are tenant-owned except global/default grading templates.

Tenant-owned result data includes:

- Result rules
- Grading scales
- Result batches
- Score sheets
- Student scores
- Computed grades
- Approval records
- Publication records
- Withheld result records
- Correction records
- Result audit references
- Result reports

## Mandatory Fields for Tenant-Owned Result Tables

Every tenant-owned result table must include:

- `tenant_id`
- `created_by`
- `updated_by`
- `created_at`
- `updated_at`
- `deleted_at`, where soft delete is allowed

Sensitive result tables should also include:

- `approved_by`
- `approved_at`
- `published_by`
- `published_at`
- `locked_by`
- `locked_at`
- `audit_reference`

---

# 8. Required Services

Block 7 must include service-layer logic for:

1. `ResultRuleService`
2. `GradingScaleService`
3. `ResultBatchService`
4. `ScoreSheetService`
5. `ScoreEntryService`
6. `ResultComputationService`
7. `ResultApprovalService`
8. `ResultPublicationService`
9. `ResultWithholdingService`
10. `ResultCorrectionService`
11. `ResultReportService`
12. `ResultEligibilityService`
13. `ResultAuditService`

Controllers must not contain result computation logic.

Computation, approval, publication, and correction must be handled by services.

---

# 9. Required Models or Data Components

The final naming may follow the project convention, but Block 7 must have data components for:

- Result rules
- Grading scales
- Grade bands
- Result batches
- Result batch courses
- Score sheets
- Student course scores
- Computed results
- Result approvals
- Result publications
- Withheld results
- Result corrections
- Result comments
- Result locks

All tenant-owned models must extend the tenant-scoped model foundation from Block 1.

---

# 10. Required Filters and Guards

Block 7 must use existing foundation filters and add result-specific guards where needed.

## Required Existing Filters

- Authentication filter
- Tenant resolution filter
- Tenant membership filter
- IAM group filter
- Operational authority filter
- CSRF filter for form submissions
- API authentication filter where applicable

## Result-Specific Guards

Implement guard logic for:

- Can configure result rules
- Can create result batch
- Can view score sheet
- Can enter score
- Can submit result
- Can review result
- Can approve result
- Can publish result
- Can withhold result
- Can request correction
- Can approve correction
- Can view student result

---

# 11. Role and Authority Matrix

## Tenant Super Admin

Can:

- Configure result settings where permitted
- Assign result-related authorities
- View result configuration
- View high-level result reports

Should not normally:

- Enter lecturer scores
- Modify published results
- Bypass approval workflow

## Tenant Admin

Can:

- Manage result settings only if granted authority
- View result reports according to authority
- Support result workflow administration

## Lecturer

Can:

- View assigned course score sheets
- Enter scores for allocated courses
- Save draft scores
- Submit score sheets
- View returned corrections

Cannot:

- Approve final result unless assigned operational authority
- Publish results
- Edit published result directly
- Access other lecturers’ courses unless authorized

## HOD

Operational authority, not IAM group.

Can:

- Review department results
- Return results for correction
- Approve departmental stage
- View departmental result reports

## Exams Officer

Operational authority, not IAM group.

Can:

- Review submitted results
- Check completeness
- Return incomplete results
- Recommend approval
- Generate result reports

## Result Approver / Academic Authority

Operational authority, not IAM group.

Can:

- Approve final result stage
- Authorize publication
- Approve corrections where configured

## Student

Can:

- View own published result
- See withheld notice where applicable
- Download or print result slip if tenant allows

Cannot:

- View another student’s result
- View unpublished result
- View result blocked by payment rule
- Access score entry or approval screens

---

# 12. Result Lifecycle

Block 7 must enforce the following standard lifecycle:

1. Result rule configured
2. Result batch created
3. Course score sheet generated
4. Score entry opened
5. Lecturer saves draft scores
6. Lecturer submits score sheet
7. Department/HOD reviews
8. Exams office reviews
9. Academic authority approves
10. Result published
11. Student views result
12. Result locked
13. Corrections handled through correction workflow only

## Lifecycle Rule

A result cannot jump lifecycle stages unless tenant configuration explicitly allows that shortcut.

Sensitive shortcuts must be audited.

---

# 13. Score Statuses

The system must support student-course score statuses:

- Draft
- Complete
- Missing CA
- Missing exam
- Absent
- Withheld
- Submitted
- Returned
- Approved
- Published
- Corrected
- Locked

---

# 14. Computed Academic Outcomes

The system must support outcome detection for:

- Passed
- Failed
- Carryover
- Resit
- Special exam pending
- Result withheld
- Missing result
- Incomplete
- Repeat candidate
- Withdrawal candidate
- Graduating candidate placeholder

Full progression automation can be handled later, but Block 7 must produce clean outcome data that future progression modules can use.

---

# 15. API and Vue Usage

## Vue Should Be Used For

- Score entry table
- Auto total preview
- Grade preview
- Missing score warnings
- Score validation
- Batch filtering
- Result review dashboards
- Approval comments
- Student result filtering
- Mobile result cards
- Correction request forms

## Full Page Reload Should Be Used For

- Final score sheet submission
- Result batch submission
- Approval decision
- Result publication
- Result locking
- Correction approval
- Withholding release

## Standard API Response

All result APIs must follow the platform API response standard:

```json
{
  "success": true,
  "message": "Operation completed successfully.",
  "data": {},
  "errors": {},
  "meta": {},
  "audit_reference": "AUD-2026-000001"
}
```

`audit_reference` is required for sensitive result actions.

---

# 16. Required Views and Screens

## Tenant Admin / Exams Screens

- Result rules index
- Create/edit grading scale
- Create/edit CA/exam ratio
- Result batch list
- Create result batch
- Batch detail
- Score sheet generation
- Result review list
- Approval queue
- Publication queue
- Withheld results list
- Correction requests list
- Result reports

## Lecturer Screens

- Assigned result batches
- Assigned course score sheets
- Score entry page
- Score draft page
- Submitted score sheets
- Returned score corrections

## HOD / Department Review Screens

- Department result batches
- Department score sheet review
- Missing score review
- Failed/carryover list
- Return for correction
- Approve department stage

## Student Screens

- My results
- Result detail
- Result slip view
- Withheld result notice
- Payment-blocked result notice where applicable

## Mobile Rules

On mobile:

- Score entry must be broken into manageable rows/cards
- Search and filters must be prominent
- Student result must display as clear cards
- Approval actions must be touch-friendly
- Tables must not overflow the viewport
- Critical buttons must be clearly separated to avoid accidental approval/publication

---

# 17. Required Reports

Block 7 must produce tenant-scoped reports.

## Result Reports

- Course score sheet
- Departmental result sheet
- Programme result sheet
- Level result sheet
- Semester result sheet
- Published result list
- Failed course list
- Carryover list
- Missing score list
- Absent student list
- Withheld result list
- Correction history report
- Approval history report
- Result publication report

## Student Reports

- Individual result slip
- Semester result summary
- Course performance summary
- Carryover summary

## Management Reports

- Department performance summary
- Programme performance summary
- Lecturer submission status
- Pending approval report
- Unpublished result report

## Export Rule

Every exported result report must show:

- Tenant name
- Department
- Programme
- Level
- Academic session
- Semester
- Report title
- Date generated
- Generated by
- Approval status
- Filters applied

---

# 18. Notification Requirements

Block 7 must prepare notification events for:

- Score sheet opened
- Score sheet returned for correction
- Score sheet submitted
- Result batch approved
- Result published
- Result withheld
- Result correction approved
- Student result available
- Student result blocked due to payment rule

Notification channels may include:

- In-app notification
- Email
- SMS where integrated later

SMS integration is not mandatory inside Block 7 unless the platform notification foundation already supports it.

---

# 19. Audit Requirements

Block 7 requires strict audit logging.

## Must Audit

- Result rule created
- Result rule updated
- Grading scale changed
- Result batch created
- Result batch opened
- Score sheet generated
- Score draft saved
- Score changed
- Score submitted
- Result reviewed
- Result approved
- Result returned
- Result rejected
- Result published
- Result withheld
- Withheld result released
- Correction requested
- Correction approved
- Correction rejected
- Result locked
- Student viewed result
- Unauthorized result access attempt
- Payment-blocked result viewing attempt

## Audit Record Must Capture

- Tenant ID
- User ID
- IAM group
- Operational authority
- Module
- Action
- Entity type
- Entity ID
- Old value where applicable
- New value where applicable
- IP address
- User agent
- Timestamp
- Status
- Human-readable summary
- Audit reference

## Immutable Audit Rule

Result audit records must not be editable or deletable by tenant users.

---

# 20. Validation Rules

## Result Rule Validation

- Grading scale must not overlap incorrectly.
- Score ranges must be valid.
- Maximum score must be defined.
- Pass mark must be within valid range.
- CA and exam maximums must match total maximum.
- Grade labels must be unique within grading scale.
- Rule scope must belong to tenant.

## Batch Validation

- Tenant must be active.
- Session must exist.
- Semester must exist.
- Department must exist.
- Programme must exist.
- Level must exist.
- Courses must exist.
- Registered students must exist.
- Course allocation must exist.

## Score Validation

- Student must belong to tenant.
- Student must be registered for course.
- Course must belong to tenant.
- Lecturer must be allocated to course.
- CA score must be numeric or intentionally marked missing.
- Exam score must be numeric or intentionally marked missing.
- Scores must not exceed configured maximum.
- Batch must be open.
- Result must not be locked.

## Approval Validation

- User must have correct operational authority.
- Approval stage must match user responsibility.
- Required previous stages must be complete.
- Result must not be locked.
- Rejection or return must include comment.

## Publication Validation

- Result must be approved.
- Result must not be locked.
- Publication user must have authority.
- Student visibility must respect tenant policy.
- Payment block must be checked where configured.

---

# 21. Edge Cases Developers Must Handle

1. Student registered course but has no score.
2. Student has CA but missed examination.
3. Student has examination score but no CA.
4. Student was absent.
5. Student result is withheld.
6. Student has outstanding fees.
7. Lecturer submits incomplete score sheet.
8. HOD returns result for correction.
9. Score changes after submission.
10. Result published accidentally.
11. Course was assigned to multiple lecturers.
12. Lecturer was removed from course after saving draft.
13. Course registration changed after score sheet generation.
14. Student changed programme after registration.
15. Student has carryover from previous session.
16. Tenant changes grading scale after results exist.
17. Result batch is locked.
18. Student views result on mobile.
19. Platform admin accesses tenant result data.
20. Attempted URL manipulation to view another tenant’s result.

---

# 22. Implementation Order for Block 7

## Step 1: Result Configuration Foundation

- Create result rule structure
- Create grading scale structure
- Create grade band structure
- Configure CA/exam ratio
- Configure pass mark
- Configure result approval workflow linkage

## Step 2: Result Batch Foundation

- Create result batch structure
- Link batch to tenant, session, semester, department, programme, level
- Add batch statuses
- Add batch permissions
- Add batch audit logging

## Step 3: Score Sheet Generation

- Pull approved course registrations from Block 6
- Pull course allocations from Block 6
- Generate course score sheets
- Prevent duplicate score sheets
- Add missing/exception handling

## Step 4: Score Entry

- Build lecturer score entry UI
- Add Vue inline validation
- Add draft save
- Add final submit
- Enforce lecturer-course authorization
- Audit score changes

## Step 5: Computation Engine

- Implement ResultComputationService
- Compute total
- Compute grade
- Compute grade point
- Compute remark
- Detect fail/carryover
- Handle missing/withheld/absent cases
- Unit-test computation rules

## Step 6: Review and Approval

- Build approval queues
- Add HOD/department review
- Add exams office review
- Add academic authority approval
- Add return-for-correction
- Add comments and approval history

## Step 7: Publication

- Build publication control
- Enforce approval completion
- Enforce payment viewing rule where configured
- Publish results
- Notify students
- Add student result view

## Step 8: Withholding and Correction

- Add result withholding
- Add release of withheld result
- Add correction request
- Add correction approval
- Add corrected result recomputation
- Add correction audit history

## Step 9: Reports

- Course score sheet report
- Department result report
- Programme result report
- Carryover report
- Missing score report
- Withheld result report
- Approval report
- Publication report

## Step 10: Final Stabilization

- Tenant isolation testing
- Authorization testing
- Computation testing
- Audit testing
- Mobile testing
- Result publication testing
- Correction workflow testing
- Payment-blocked result viewing testing

---

# 23. Testing Checklist

## Tenant Isolation Tests

- Tenant A cannot see Tenant B result rules.
- Tenant A cannot see Tenant B batches.
- Tenant A cannot see Tenant B score sheets.
- Tenant A cannot see Tenant B student results.
- Tenant A cannot access Tenant B result URL by changing IDs.
- Tenant A lecturer cannot submit Tenant B course scores.

## Authorization Tests

- Lecturer can enter only assigned course scores.
- Lecturer cannot approve final result.
- Student cannot access score entry.
- HOD can review only scoped department result.
- Exams officer can review only authorized result batches.
- Tenant admin without result authority cannot publish results.
- Student can view only own published result.

## Computation Tests

- Correct total is computed.
- Correct grade is computed.
- Boundary scores produce correct grade.
- Missing CA is handled.
- Missing exam is handled.
- Absent student is handled.
- Withheld result is handled.
- Carryover is detected.
- Course-specific grading overrides programme grading where configured.

## Workflow Tests

- Batch cannot publish before approval.
- Returned result can be corrected.
- Rejected result cannot publish.
- Published result cannot be directly edited.
- Locked result cannot be changed.
- Correction creates audit history.
- Withheld result does not display normal result.

## Payment Rule Tests

- Student with required payment can view result.
- Student with unpaid required fee is blocked where tenant configured this rule.
- Payment-blocked attempt is logged.
- Payment rule does not affect tenants that disabled result-payment control.

## Mobile Tests

- Lecturer can enter scores on mobile.
- Student can view result on mobile.
- Approval buttons are usable on mobile.
- Result cards are readable.
- Tables do not break small screens.

## Audit Tests

- Score change is audited.
- Approval is audited.
- Publication is audited.
- Correction is audited.
- Withholding is audited.
- Unauthorized result access is audited.

---

# 24. Acceptance Criteria

Block 7 is complete only when:

1. Tenant can configure result rules.

2. Tenant can configure grading scales.

3. Result batches can be created by session, semester, department, programme, and level.

4. Course score sheets are generated from approved course registrations.

5. Lecturers can enter scores only for allocated courses.

6. System computes totals, grades, remarks, and carryover status correctly.

7. Missing, absent, withheld, resit, and special exam cases are handled.

8. Result approval workflow works according to tenant configuration.

9. Approved results can be published.

10. Students can view only their own published results.

11. Payment rule can block result viewing where tenant configures it.

12. Published results cannot be edited directly.

13. Result correction workflow is implemented and audited.

14. Result withholding workflow is implemented and audited.

15. Result locking is implemented.

16. Result reports are available.

17. All sensitive actions are audited.

18. Tenant isolation is enforced across all result data.

19. Mobile result views are usable.

20. No result rule, grading scale, or tenant-owned display data is hard-coded.

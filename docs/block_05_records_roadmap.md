# Block 5 Strategic Implementation Roadmap  
## Student and Staff Records Block  
### SaaS Multi-Tenant SHST Information Management System

---

## Document Status

**Block Number:** Block 5  
**Block Name:** Student and Staff Records  
**System Type:** SaaS multi-tenant information management system  
**Target Institutions:** Nigerian Schools of Health Sciences and Technology  
**Framework:** CodeIgniter 4  
**Authentication Foundation:** CodeIgniter Shield  
**Frontend Enhancement:** Vue.js Composition API where appropriate  
**Primary Users:** Tenant Super Admin, Tenant Admin, Registrar, Admission Officer, Student Records Officer, Staff Records Officer, HOD, Lecturer, Student  
**Dependency:** Block 1, Block 3, and Block 4 foundations must already exist or be stubbed consistently.  

---

# 1. Block 5 Purpose

Block 5 establishes the official **student record center** and **staff record center** for each tenant institution.

This block converts the platform from a configuration/admission/payment system into a reliable institutional records system.

The main purpose is to ensure that each school has one trusted source of truth for:

- Student identity
- Student academic profile
- Student admission background
- Student department/programme/level placement
- Student lifecycle status
- Staff identity
- Staff employment profile
- Staff departmental placement
- Staff academic and administrative responsibilities
- Staff lifecycle status

This block must not implement course allocation, course registration, result processing, or staff payroll. Those belong to later or separate blocks.

---

# 2. Strategic Importance

Student and staff records are the operational backbone of the SHST SaaS platform.

Later blocks depend heavily on this block:

| Later Block/Feature | Dependency on Block 5 |
|---|---|
| Course allocation | Requires staff records, department assignment, lecturer identity |
| Course registration | Requires active student records, programme, level, department |
| Result processing | Requires student records, staff records, lecturer identity, student academic status |
| Fees enforcement | Requires student status, programme, level, student category |
| Clearance | Requires student records and lifecycle history |
| Transcript | Requires stable student academic identity |
| Professional council reporting | Requires student biodata, programme, department, admission details |
| Audit/reporting | Requires reliable user/student/staff identity |

If Block 5 is weak, every later academic block becomes unreliable.

---

# 3. Block 5 Scope

## 3.1 In Scope

Block 5 includes:

1. Student profile management  
2. Student lifecycle/status management  
3. Student academic placement  
4. Student biodata and contact records  
5. Student guardian/sponsor/next-of-kin records  
6. Student admission background linkage  
7. Student document/file references  
8. Student record correction workflow  
9. Staff profile management  
10. Staff lifecycle/status management  
11. Staff departmental placement  
12. Staff qualification and professional records  
13. Staff academic/administrative responsibility placeholders  
14. Staff document/file references  
15. Student and staff search/filter/listing  
16. Student and staff reports  
17. Student and staff audit logging  
18. Mobile-first record management screens  

---

## 3.2 Out of Scope

Block 5 must not implement the following as full features:

- Student application form creation
- Admission screening
- Admission offer workflow
- Payment gateway integration
- Fee schedule configuration
- Course allocation
- Course registration
- Result entry
- Result computation
- Result approval
- Transcript generation
- Graduation clearance
- Staff payroll
- Staff attendance
- HR appraisal
- Biometric attendance
- Hostel allocation
- Library borrowing
- Professional council integration

However, Block 5 must provide the record structure needed by these future modules.

---

# 4. Required Foundation Alignment

Block 5 must strictly follow the foundation-concept documentation.

## Mandatory Alignment Rules

1. Every student and staff record must be tenant-scoped.

2. No student or staff record from one tenant must be visible to another tenant.

3. All student and staff display data must come from the database.

4. All protected actions must enforce CodeIgniter Shield authentication.

5. Authorization must use the two-layer model:
   - IAM group
   - Operational authority

6. All student and staff sensitive actions must be audited.

7. All screens must be mobile-first.

8. Vue Composition API may be used for dynamic forms, filtering, dependent dropdowns, and progressive saves.

9. Public website pages must not expose internal student or staff records unless explicitly published as public profile content in the public website block.

10. Staff operational responsibilities must not become Shield groups unless they are true identity classes.

---

# 5. Block 5 Users and Responsibilities

## 5.1 Tenant Super Admin

Can:

- View all student and staff records within the tenant
- Configure record-related settings where provided
- Approve sensitive corrections if given authority
- View audit logs for records
- Restore or deactivate records where permitted

Cannot:

- Access records belonging to another tenant
- Override audit trail
- Bypass lifecycle rules without proper authority

---

## 5.2 Tenant Admin

Can:

- Manage student/staff records depending on assigned operational authority
- View tenant record reports
- Update allowed fields
- Search and filter records

Cannot:

- Perform sensitive actions without authority
- Access another tenant
- Delete audit history

---

## 5.3 Registrar / Student Records Officer

Operational authority responsible for:

- Maintaining official student records
- Confirming student biodata
- Correcting student administrative information
- Managing student status changes where authorized
- Producing student lists and reports

---

## 5.4 Staff Records Officer / HR Officer

Operational authority responsible for:

- Creating and maintaining staff records
- Updating staff employment information
- Managing staff lifecycle status
- Maintaining staff qualification records

---

## 5.5 HOD

Operational authority responsible for:

- Viewing students in assigned department
- Viewing staff in assigned department
- Confirming department-level student/staff placement
- Reviewing department lists

HOD must not automatically receive full tenant-wide records access.

---

## 5.6 Lecturer

Can:

- View own staff profile
- View future assigned students only through later academic blocks
- View limited department information where allowed

Cannot in Block 5:

- Edit student records
- Create staff records
- Change student status
- View all student records unless granted operational authority

---

## 5.7 Student

Can:

- View own profile
- Update limited self-service information if enabled
- Upload or manage permitted personal documents if enabled
- See official status and programme placement

Cannot:

- Change programme/department/level
- Change admission number
- Change academic status
- Edit approved institutional records
- Access another student’s profile

---

# 6. Core Modules Inside Block 5

---

## Module 1: Student Record Foundation

### Purpose

To create and maintain the official student profile for each tenant.

### Required Capabilities

The system must support:

- Creating student record manually by authorized staff
- Creating student record from admitted applicant after Block 3
- Viewing student record
- Editing permitted student fields
- Managing student lifecycle status
- Linking student to department
- Linking student to programme
- Linking student to level
- Linking student to academic session admitted
- Assigning student number/admission number/matric number where applicable
- Uploading student passport and documents
- Viewing student record history
- Searching and filtering student records

### Required Student Record Sections

Each student profile must be organized into clear sections:

1. Identity information  
2. Contact information  
3. Admission information  
4. Academic placement  
5. Guardian/sponsor/next-of-kin information  
6. O'Level/admission credential summary  
7. Payment summary reference  
8. Document/file references  
9. Status and lifecycle history  
10. Audit/history log summary  

---

## Module 2: Student Identity and Biodata

### Required Fields

Student biodata must support:

- Tenant ID
- Student system ID
- Application reference, where applicable
- Admission number
- Matric number/student number, where applicable
- Surname
- First name
- Other name
- Gender
- Date of birth
- Marital status, where required
- Nationality
- State of origin
- LGA of origin
- Religion, where institutionally required
- Passport photo reference
- Phone number
- Email address
- Residential address
- Contact address
- Status

### Nigeria-Aware Rules

The system must support:

- Nigerian phone number normalization
- Nigerian states and LGAs
- Names with local spelling patterns
- Students without email at initial capture
- Multiple phone number formats
- Guardian/sponsor phone number as fallback communication contact

### Duplicate Prevention

The system must check for likely duplicate student records using combinations such as:

- Application reference
- Admission number
- Matric/student number
- Phone number
- Email address
- Full name + date of birth + programme
- O'Level examination number where captured

Duplicate checking must warn authorized users but must not automatically merge records.

---

## Module 3: Student Academic Placement

### Purpose

To place each student correctly within the tenant academic structure created in Block 1.

### Required Placement Data

Each student must be linked to:

- Tenant
- Department
- Programme
- Current level
- Admission session
- Current session
- Entry mode, where applicable
- Student category, where applicable
- Student set/batch, where applicable
- Study mode, where applicable

### Supported Student Categories

The system must support configurable categories such as:

- Fresh student
- Returning student
- Direct entry student
- Transfer student
- Spillover student
- Repeating student
- Part-time student, where applicable
- Custom tenant-defined category

### Mandatory Rule

Student academic placement must reference tenant-owned academic configuration.

A student cannot be assigned to:

- A department outside the tenant
- A programme outside the tenant
- A level outside the tenant
- An inactive programme unless specifically allowed by tenant policy

---

## Module 4: Student Lifecycle and Status Management

### Purpose

To control student status changes in a structured and auditable way.

### Required Student Statuses

The system must support:

- Active
- Deferred
- Suspended
- Withdrawn
- Graduated
- Cleared graduate
- Archived
- Deceased, where needed
- Transferred out, where needed

### Status Change Rules

Every status change must capture:

- Previous status
- New status
- Reason
- Effective date
- Authorized by
- Supporting document reference where applicable
- Audit reference

### Sensitive Statuses

The following status changes must require explicit authority:

- Active to Suspended
- Active to Withdrawn
- Active to Graduated
- Suspended to Active
- Withdrawn to Active
- Graduated to Active
- Any archival action

### Block Boundary Rule

Block 5 may manage status values, but academic progression logic belongs to later academic/result blocks.

Example:

- Block 5 can record that a student is active or withdrawn.
- Later result/progression blocks decide promotion, repeat, carryover, or graduation eligibility.

---

## Module 5: Guardian, Sponsor, and Next-of-Kin Records

### Purpose

To support Nigerian institutional realities where guardians, sponsors, or next-of-kin are often required for student records.

### Required Capabilities

The system must support:

- Next of kin details
- Guardian details
- Sponsor details
- Emergency contact details
- Relationship to student
- Phone number
- Address
- Occupation, where required
- Email, where available

### Rule

A student may have more than one related contact, but one must be marked as primary emergency contact.

---

## Module 6: Student Documents and Files

### Purpose

To safely store references to student-related files.

### Supported File Types

The system must support references to:

- Passport photograph
- Admission letter
- Acceptance evidence
- Birth certificate/declaration of age
- State/LGA indigene certificate
- O'Level result copies
- Medical fitness certificate
- Change of name document, where applicable
- Clearance documents
- Custom tenant-defined document type

### File Access Rule

All student files must be tenant-scoped and visibility-controlled.

A student may only access files marked as visible to the student.

Administrative files may be visible only to authorized tenant officers.

---

## Module 7: Student Self-Service Profile

### Purpose

To allow students to view their official profile and update limited personal information where permitted.

### Required Capabilities

Student self-service may allow:

- View biodata
- View academic placement
- View admission details
- View current status
- Update phone number, if permitted
- Update contact address, if permitted
- Update guardian contact, if permitted
- Upload permitted documents, if enabled
- Submit correction request for locked fields

### Locked Fields

Students must not directly edit:

- Admission number
- Matric number
- Programme
- Department
- Level
- Admission session
- Current academic status
- Result-related fields
- Payment-related fields

### Correction Request Rule

If a student sees an error in locked data, the student may submit a correction request if enabled.

The correction request must go through authorized staff approval.

---

## Module 8: Student Record Correction Workflow

### Purpose

To prevent uncontrolled changes to official student records.

### Required Capabilities

The system must support:

- Correction request creation
- Correction type selection
- Old value capture
- Proposed new value
- Reason for correction
- Supporting document upload
- Review by authorized officer
- Approval or rejection
- Audit trail

### Correction Types

Supported correction types may include:

- Name correction
- Date of birth correction
- Phone number correction
- Email correction
- State/LGA correction
- Programme correction
- Department correction
- Level correction
- Admission number correction
- Passport correction

### Sensitive Correction Rule

Sensitive corrections must require higher authority.

Sensitive fields include:

- Name
- Date of birth
- Admission number
- Matric number
- Programme
- Department
- Level
- Admission session

---

# 7. Staff Record Modules

---

## Module 9: Staff Record Foundation

### Purpose

To create and maintain official staff profiles for each tenant.

### Required Capabilities

The system must support:

- Creating staff record
- Viewing staff profile
- Editing permitted staff fields
- Assigning staff to department
- Assigning staff category
- Assigning employment type
- Managing staff lifecycle status
- Recording staff qualifications
- Recording professional qualifications
- Uploading staff passport and documents
- Linking staff record to system user account
- Searching and filtering staff records

---

## Module 10: Staff Identity and Biodata

### Required Fields

Staff biodata must support:

- Tenant ID
- Staff system ID
- Staff number
- Surname
- First name
- Other name
- Gender
- Date of birth, where required
- Marital status, where required
- Nationality
- State of origin
- LGA of origin
- Phone number
- Email address
- Residential address
- Contact address
- Passport photo reference
- Status

### Nigeria-Aware Rules

The system must support:

- Staff with phone number but no email
- Staff with personal email and institutional email
- Local address formats
- Professional titles and ranks commonly used in Nigerian institutions

---

## Module 11: Staff Employment and Departmental Placement

### Required Employment Data

Staff records must support:

- Department
- Unit, where applicable
- Staff category
- Employment type
- Rank/designation
- Appointment date
- Confirmation status, where applicable
- Job title
- Work location/campus, where applicable
- Reporting line, where applicable

### Staff Categories

The system must support:

- Academic staff
- Non-academic staff
- Registry staff
- Bursary staff
- Admission staff
- Examination staff
- Student affairs staff
- ICT staff
- Departmental staff
- Management staff
- Part-time lecturer
- External examiner/supervisor
- Custom tenant-defined staff category

### Employment Types

The system must support:

- Full-time
- Part-time
- Contract
- Visiting
- Temporary
- Consultant
- Custom tenant-defined type

### Placement Rule

A staff member may belong to one primary department and may have additional cross-department responsibilities through operational authority or later academic allocation.

---

## Module 12: Staff Qualifications and Professional Records

### Purpose

To support academic credibility, accreditation readiness, and professional council expectations.

### Required Capabilities

The system must support multiple qualification records per staff member.

### Qualification Fields

Each qualification should support:

- Qualification title
- Institution obtained from
- Field of study
- Year obtained
- Certificate file reference
- Qualification type
- Verification status

### Professional Records

The system must support:

- Professional body name
- Registration number
- Licence number, where applicable
- Licence expiry date, where applicable
- Membership status
- Certificate/document reference

### Rule

Professional qualification fields must be flexible because different departments may require different professional bodies.

---

## Module 13: Staff Lifecycle and Status Management

### Required Staff Statuses

The system must support:

- Active
- On leave
- Suspended
- Transferred
- Retired
- Resigned
- Terminated
- Inactive
- Deceased, where needed

### Status Change Rules

Every status change must capture:

- Previous status
- New status
- Reason
- Effective date
- Authorized by
- Supporting document reference where applicable
- Audit reference

### Sensitive Status Rule

Suspension, termination, retirement, and reactivation must require higher authority.

---

## Module 14: Staff User Account Linkage

### Purpose

To connect staff records to authenticated system users.

### Required Capabilities

The system must support:

- Staff without login account
- Staff with login account
- Linking existing user account to staff record
- Creating user account from staff record
- Assigning IAM group
- Assigning operational authority
- Suspending staff user access without deleting staff record

### Mandatory Rule

Staff profile is not the same as login identity.

A staff member can exist in staff records without portal login.

A user account can be linked to a staff record only within the same tenant.

---

# 8. Student and Staff Search

## Required Capabilities

The system must provide mobile-friendly search and filtering.

### Student Search Filters

- Name
- Admission number
- Matric/student number
- Phone number
- Department
- Programme
- Level
- Session admitted
- Current status
- Student category

### Staff Search Filters

- Name
- Staff number
- Phone number
- Email
- Department
- Staff category
- Employment type
- Rank/designation
- Current status

### Mobile Rule

Search results must display as cards on mobile and tables on desktop.

---

# 9. Data Ownership and Database Rules

## Student-Owned Data Scope

All student-related records must include `tenant_id`.

This includes:

- Students
- Student contacts
- Student academic placement
- Student documents
- Student status history
- Student correction requests
- Student identifiers

## Staff-Owned Data Scope

All staff-related records must include `tenant_id`.

This includes:

- Staff
- Staff qualifications
- Staff professional records
- Staff documents
- Staff status history
- Staff user linkage
- Staff correction records

## Mandatory Metadata

Major records must include:

- `tenant_id`
- `created_by`
- `updated_by`
- `created_at`
- `updated_at`
- `deleted_at`, where soft delete is allowed

## Soft Delete Rule

Student and staff records must not be permanently deleted through normal UI.

Use status change or soft delete depending on policy.

Permanent deletion, if ever supported, must be platform-controlled and audited.

---

# 10. Required Services

Block 5 must include service-layer logic for:

1. StudentProfileService  
2. StudentStatusService  
3. StudentPlacementService  
4. StudentDocumentService  
5. StudentCorrectionRequestService  
6. StudentSearchService  
7. StaffProfileService  
8. StaffStatusService  
9. StaffQualificationService  
10. StaffProfessionalRecordService  
11. StaffUserLinkageService  
12. StaffSearchService  
13. RecordDuplicateCheckService  
14. RecordAuditService  
15. RecordReportService  

Controllers must not contain heavy business logic.

---

# 11. Required Controllers

## Tenant Admin Controllers

- StudentRecordsController
- StudentStatusController
- StudentCorrectionController
- StaffRecordsController
- StaffStatusController
- StaffQualificationController
- StaffUserLinkageController
- RecordsReportController

## Student Portal Controllers

- StudentProfileController
- StudentCorrectionRequestController

## Lecturer/Staff Portal Controllers

- StaffProfileController

## API Controllers

- Api/StudentRecordsController
- Api/StudentSearchController
- Api/StaffRecordsController
- Api/StaffSearchController
- Api/RecordCorrectionController

---

# 12. Required Models

## Student Models

- StudentModel
- StudentContactModel
- StudentPlacementModel
- StudentDocumentModel
- StudentStatusHistoryModel
- StudentCorrectionRequestModel
- StudentIdentifierModel

## Staff Models

- StaffModel
- StaffQualificationModel
- StaffProfessionalRecordModel
- StaffDocumentModel
- StaffStatusHistoryModel
- StaffUserLinkModel

All tenant-owned models must extend the tenant-scoped base model from Block 1.

---

# 13. Required Views and Screens

## Tenant Admin Screens

1. Student list
2. Student profile view
3. Create student record
4. Edit student record
5. Student academic placement
6. Student status history
7. Student document list
8. Student correction requests
9. Staff list
10. Staff profile view
11. Create staff record
12. Edit staff record
13. Staff department placement
14. Staff qualifications
15. Staff professional records
16. Staff status history
17. Staff user account linkage
18. Records reports

## Student Screens

1. My profile
2. My academic information
3. My contact information
4. My documents, where enabled
5. Correction request form
6. Correction request status

## Lecturer/Staff Screens

1. My staff profile
2. My contact details
3. My qualification records
4. My professional records

---

# 14. Vue Composition API Usage

Vue should be used for:

- Student profile multi-step forms
- Staff profile multi-step forms
- Dependent dropdowns for department/programme/level
- Nigerian state and LGA selection
- Search and filter panels
- Document upload previews
- Duplicate warning prompts
- Correction request comparison
- Staff qualification dynamic rows
- Professional record dynamic rows
- Status change forms
- Mobile-friendly card/table switching where useful

Full page reload should be used for:

- Final creation of official student record
- Final creation of official staff record
- Sensitive status changes
- Approval of correction requests
- Linking staff to user account
- Actions requiring full server confirmation

---

# 15. Validation Rules

## Student Validation

Student records must validate:

- Required names
- Valid phone number format
- Valid email if provided
- Valid gender value
- Valid state and LGA
- Valid tenant department
- Valid tenant programme
- Valid tenant level
- Valid admission session
- Unique admission number within tenant where provided
- Unique matric/student number within tenant where provided
- Duplicate warning checks

## Staff Validation

Staff records must validate:

- Required names
- Valid phone number format
- Valid email if provided
- Valid staff number where required
- Valid department
- Valid staff category
- Valid employment type
- Valid status
- Duplicate warning checks

## Status Validation

Status changes must validate:

- Previous status
- New status
- Allowed transition
- Reason
- Effective date
- User authority
- Tenant scope

## Correction Request Validation

Correction requests must validate:

- Target record
- Target field
- Old value
- Proposed value
- Reason
- Supporting document where required
- Reviewer authority

---

# 16. Audit Requirements

Block 5 must audit the following actions.

## Student Audit Events

- Student record created
- Student record updated
- Student profile viewed by admin
- Student status changed
- Student department changed
- Student programme changed
- Student level changed
- Student document uploaded
- Student document deleted/disabled
- Student correction requested
- Student correction approved
- Student correction rejected
- Student record archived
- Student user profile viewed
- Student self-service update submitted

## Staff Audit Events

- Staff record created
- Staff record updated
- Staff profile viewed by admin
- Staff status changed
- Staff department changed
- Staff qualification added
- Staff qualification updated
- Staff professional record added
- Staff professional record updated
- Staff document uploaded
- Staff user account linked
- Staff user account unlinked
- Staff record archived

## Audit Record Must Capture

- Tenant ID
- Acting user ID
- Acting user group
- Operational authority used
- Module
- Entity type
- Entity ID
- Action
- Old value where necessary
- New value where necessary
- IP address
- User agent
- Timestamp
- Status
- Human-readable summary

---

# 17. Reports Required in Block 5

## Student Reports

The system must provide:

- All students list
- Active students list
- Students by department
- Students by programme
- Students by level
- Students by admission session
- Students by status
- Students with incomplete profiles
- Students with missing documents
- Student population summary

## Staff Reports

The system must provide:

- All staff list
- Active staff list
- Staff by department
- Staff by category
- Staff by employment type
- Academic staff list
- Non-academic staff list
- Staff qualification summary
- Staff with missing documents
- Staff status summary

## Export Rule

Reports must be exportable only by authorized users.

Each export must include:

- Tenant name
- Report title
- Date generated
- Generated by
- Filters applied
- Total records

---

# 18. Dashboard Requirements

## Tenant Admin Records Dashboard

Must show:

- Total students
- Active students
- Suspended/withdrawn students
- Graduated students
- Total staff
- Academic staff
- Non-academic staff
- Staff by department
- Records needing correction review
- Incomplete student profiles
- Incomplete staff profiles

## Student Dashboard Integration

Student dashboard must show:

- Student name
- Student passport
- Admission/matric number
- Department
- Programme
- Current level
- Current status
- Department color identity
- Profile completion status

## Lecturer/Staff Dashboard Integration

Staff dashboard must show:

- Staff name
- Staff passport
- Staff number
- Department
- Staff category
- Current status
- Profile completion status

---

# 19. Side Navigation Requirements

Block 5 must add navigation items through the central navigation resolver only.

## Tenant Admin Navigation

Possible menu items:

- Records Dashboard
- Students
- Student Corrections
- Staff
- Staff Qualifications
- Staff User Links
- Records Reports

## Student Navigation

Possible menu items:

- My Profile
- My Academic Info
- My Documents
- Correction Requests

## Lecturer Navigation

Possible menu items:

- My Staff Profile
- My Qualifications
- My Professional Records

Menu visibility must depend on:

- IAM group
- Operational authority
- Tenant membership
- Module enablement
- User status

---

# 20. Notification Requirements

Block 5 should prepare notification events for:

- Student record created
- Student profile correction approved
- Student profile correction rejected
- Student status changed
- Staff record created
- Staff user account linked
- Staff status changed
- Correction request awaiting review

Notifications may initially be in-app only.

SMS/email delivery can be implemented later if the notification foundation is not yet complete.

---

# 21. Security Rules

## Student Records Security

- Students can only view their own record.
- Tenant admins can only view records within their tenant.
- HODs can only view records within assigned department unless broader authority is granted.
- Sensitive fields require authority to edit.
- Programme/department/level changes must be audited.
- File access must enforce tenant and visibility checks.

## Staff Records Security

- Staff can view their own profile.
- Staff cannot edit locked employment data.
- Tenant admins can only manage staff in their tenant.
- HODs can view department staff where authority permits.
- Staff-user account linking must be restricted.
- Staff status changes must be audited.

---

# 22. Implementation Order for Block 5

Developers must implement Block 5 in this order.

## Step 1: Confirm Dependencies

Confirm that the following from previous blocks exist:

- Tenant resolver
- Tenant-scoped base model
- IAM groups
- Operational authority foundation
- Audit service
- File/media foundation
- Academic sessions
- Departments
- Programmes
- Levels
- Payment summary hooks from Block 4, where available
- Admission conversion hooks from Block 3, where available

## Step 2: Student Core Records

Implement:

- Student model
- Student profile service
- Student list
- Student create/edit
- Student profile view
- Student academic placement
- Student duplicate warning

## Step 3: Student Lifecycle

Implement:

- Student status history
- Status change service
- Status transition validation
- Status change audit logging

## Step 4: Student Contacts and Documents

Implement:

- Guardian/sponsor/next-of-kin records
- Student document references
- File access checks
- Student document upload interface

## Step 5: Student Self-Service

Implement:

- Student profile page
- Limited editable fields
- Correction request submission
- Correction request status tracking

## Step 6: Staff Core Records

Implement:

- Staff model
- Staff profile service
- Staff list
- Staff create/edit
- Staff profile view
- Staff department placement
- Staff duplicate warning

## Step 7: Staff Lifecycle and Qualifications

Implement:

- Staff status history
- Staff qualification records
- Professional records
- Staff document references

## Step 8: Staff User Linkage

Implement:

- Link staff to user account
- Create user from staff profile where authorized
- Assign lecturer IAM group where appropriate
- Enforce same-tenant linkage

## Step 9: Reports and Dashboards

Implement:

- Student reports
- Staff reports
- Records dashboard
- Exports
- Profile completion indicators

## Step 10: Final Stabilization

Perform:

- Tenant isolation tests
- Authorization tests
- Audit tests
- Mobile UI tests
- Duplicate prevention tests
- File access tests

---

# 23. Testing Checklist

## Tenant Isolation Tests

- Tenant A cannot see Tenant B students.
- Tenant A cannot see Tenant B staff.
- Tenant A cannot access Tenant B student profile by URL ID.
- Tenant A cannot access Tenant B staff profile by URL ID.
- Tenant A cannot access Tenant B files.
- Tenant A cannot link a user to Tenant B staff record.

## Student Record Tests

- Authorized user can create student record.
- Duplicate warning appears for likely duplicate.
- Student can be assigned only to same-tenant department.
- Student can be assigned only to same-tenant programme.
- Student status change is audited.
- Locked fields cannot be changed by student.
- Student can view only own profile.

## Staff Record Tests

- Authorized user can create staff record.
- Staff can be assigned only to same-tenant department.
- Staff qualification can be added.
- Staff professional record can be added.
- Staff status change is audited.
- Staff user account linkage enforces tenant membership.
- Staff can view own profile.

## Authorization Tests

- Registrar can manage students where authority permits.
- HOD can view department students only.
- HR/staff records officer can manage staff where authority permits.
- Lecturer cannot edit student records.
- Student cannot access admin records.
- Tenant admin cannot access platform records.

## Audit Tests

- Student creation is audited.
- Student update is audited.
- Student status change is audited.
- Staff creation is audited.
- Staff update is audited.
- Staff-user linkage is audited.
- Correction approval/rejection is audited.

## Mobile Tests

- Student profile is readable on mobile.
- Staff profile is readable on mobile.
- Search results display properly as cards.
- Multi-step forms work on mobile.
- Upload controls are mobile-friendly.
- Tables do not overflow badly.

---

# 24. Acceptance Criteria

Block 5 is complete only when:

1. Tenant admins can manage student records within their tenant.

2. Tenant admins can manage staff records within their tenant.

3. Students can view their own profile.

4. Staff can view their own profile.

5. Student records link correctly to tenant departments, programmes, levels, and sessions.

6. Staff records link correctly to tenant departments and staff categories.

7. Student and staff lifecycle statuses are controlled and audited.

8. Student and staff documents are tenant-scoped and visibility-controlled.

9. Student and staff search/filter work on mobile and desktop.

10. Correction request workflow exists for sensitive student data.

11. Staff-user account linkage works safely within tenant scope.

12. All sensitive actions are audited.

13. Reports are tenant-scoped.

14. No cross-tenant record leakage is possible.

15. No student/staff tenant data is hard-coded.

16. UI follows the established layouts and mobile-first policy.
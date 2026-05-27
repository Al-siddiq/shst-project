# Block 3 Strategic Implementation Roadmap
## Student Application and Admission System

**Project:** SaaS Multi-Tenant SHST Information Management System  
**Block:** 3 of 7  
**Block Name:** Student Application and Admission System  
**Primary Users:** Applicants, Admission Officers, Registrar, Tenant Admins, Tenant Super Admins, Platform Admins  
**Framework:** CodeIgniter 4  
**Authentication Foundation:** CodeIgniter Shield  
**Frontend Enhancement:** Vue.js Composition API where appropriate  
**Design Principle:** Nigeria-aware, mobile-first, tenant-configurable, database-driven  
**Status:** Developer and AI Coding Agent Reference Document

---

# 1. Block 3 Purpose

Block 3 delivers the full **student application and admission management foundation** for the SaaS SHST platform.

The goal is to allow any tenant institution to open an admission cycle, publish available programmes for application, receive applicant submissions, screen applicants, offer admission, track acceptance, handle clearance status, and prepare admitted applicants for conversion into student records.

This block must strictly depend on Block 1 foundations:

- Tenant resolution
- Tenant-scoped models
- Tenant profile
- Academic sessions
- Departments
- Programmes
- Levels
- Roles and authorities
- Audit trail
- Side navigation resolver
- Theme resolver
- Standard layouts

This block must also connect properly with Block 2:

- Public website programme pages
- Public admission information pages
- Public application call-to-action
- Tenant-branded admission entry point

---

# 2. Block 3 Strategic Objective

At the end of Block 3, each tenant school must be able to:

1. Configure admission cycles.
2. Select programmes open for application.
3. Define programme-specific admission requirements.
4. Receive applications through the public website.
5. Allow applicants to complete application forms.
6. Track application status.
7. Manage screening and shortlisting.
8. Offer, reject, or waitlist applicants.
9. Publish admission lists.
10. Allow applicants to accept admission.
11. Track acceptance and clearance status.
12. Prepare admitted applicants for student record creation in Block 5.
13. Maintain full tenant isolation and audit trail across all admission activities.

---

# 3. Block 3 Scope

## 3.1 In Scope

Block 3 includes:

1. Admission cycle setup
2. Admission programme opening
3. Admission requirement configuration
4. Applicant account/access flow
5. Applicant profile capture
6. O'Level result capture
7. Document upload for admission
8. Application submission
9. Application status tracking
10. Admission officer review interface
11. Screening status management
12. Shortlisting
13. Admission offer
14. Admission rejection
15. Waitlist handling
16. Admission list generation
17. Admission list publication
18. Applicant acceptance flow
19. Clearance status placeholder
20. Admission reports
21. Admission audit trail
22. Admission notification hooks

---

## 3.2 Out of Scope

The following must not be fully implemented in Block 3:

1. Full payment engine
2. Online payment gateway integration
3. Student fee management
4. Comprehensive student profile management
5. Course registration
6. Result processing
7. Staff HR records
8. Transcript generation
9. Graduation clearance
10. Professional council reporting

## Payment Boundary Decision

Admission requires application fee and acceptance fee awareness, but the full payment engine belongs to **Block 4: Fees and Levy Payment System**.

Therefore, Block 3 shall implement:

- Admission payment checkpoints
- Payment status placeholders
- Payment requirement flags
- Payment dependency contracts
- Manual status hooks where necessary

Block 3 shall not build the full invoicing, reconciliation, gateway callback, debtor, or revenue reporting engine.

---

# 4. Block 3 Dependency Rules

## 4.1 Required Completed Foundations

Before implementing Block 3, the following from Block 1 must exist:

- Tenant resolver
- Tenant-scoped base model
- Tenant profile
- Departments
- Programmes
- Academic sessions
- Levels
- Role and authority foundation
- Audit system
- Tenant admin layout
- Public theme resolver
- File/media foundation
- API response foundation

## 4.2 Required Completed Public Website Foundation

Before implementing applicant-facing pages, the following from Block 2 must exist:

- Tenant public website routing
- Tenant public layout
- Programme display pages
- Admission information page
- Public website content management foundation
- Public media handling
- Published/draft content visibility

---

# 5. Non-Negotiable Rules for Block 3

1. Every applicant record must be tenant-scoped.

2. Every admission cycle must belong to one tenant.

3. Every application must belong to one tenant and one admission cycle.

4. Every application must be linked to a tenant programme.

5. Programme names, entry requirements, admission dates, fees, screening rules, and application instructions must come from the database.

6. No school-specific admission rule shall be hard-coded.

7. Applicant data must never leak across tenants.

8. Public application forms must resolve tenant context before showing programmes.

9. Applicant phone numbers must support Nigerian phone formats and be normalized.

10. Applicants may use phone number or email for identity/access, based on Shield customization.

11. Admission officers must only review applications for their tenant.

12. Department-scoped officers must only review applications within their assigned scope where applicable.

13. Admission offer, rejection, publication, and clearance decisions must be audited.

14. Application document uploads must be tenant-scoped and protected.

15. All applicant-facing pages must be mobile-first.

16. Vue Composition API may be used for dynamic form steps, dependent dropdowns, O'Level entries, uploads, and application previews.

17. Block 3 must not create final student academic records directly; it prepares the accepted and cleared applicant for Block 5 student profile creation.

---

# 6. Core Modules Inside Block 3

---

## Module 1: Admission Cycle Setup

### Purpose

To allow each tenant school to open and manage admission periods.

### Required Capabilities

Tenant admin or authorized admission officer must be able to:

- Create admission cycle
- Select academic session
- Define admission title
- Define application opening date
- Define application closing date
- Define admission cycle status
- Define application instructions
- Define screening instructions
- Define admission list publication settings
- Open or close application
- Archive admission cycle

### Required Admission Cycle Statuses

- Draft
- Scheduled
- Open
- Closed
- Under Review
- Admission Published
- Archived

### Required Fields

Admission cycle should support:

- Tenant ID
- Academic session ID
- Admission title
- Admission code
- Opening date
- Closing date
- Status
- Public visibility
- Instructions
- Screening instructions
- Created by
- Updated by
- Created at
- Updated at

### Business Rules

1. A tenant may have multiple admission cycles.
2. Only one active public admission cycle should be shown by default unless the tenant explicitly enables multiple.
3. Applications cannot be submitted to a closed cycle.
4. Admission cycle dates must respect tenant timezone.
5. Admission cycle must be tied to a valid tenant academic session.

### Audit Events

- Admission cycle created
- Admission cycle updated
- Admission cycle opened
- Admission cycle closed
- Admission cycle archived
- Admission cycle publication setting changed

---

## Module 2: Admission Programme Opening

### Purpose

To allow tenants to define which programmes are available for application within a specific admission cycle.

### Required Capabilities

Authorized users must be able to:

- Add programme to admission cycle
- Remove programme from admission cycle
- Set programme application quota
- Set programme-specific instructions
- Set programme-specific application status
- Set programme-specific screening method
- Set programme-specific admission requirement
- Define target level of entry where applicable

### Required Fields

Admission programme record should support:

- Tenant ID
- Admission cycle ID
- Programme ID
- Department ID
- Entry level ID, where applicable
- Application quota
- Screening method
- Programme instruction
- Requirement summary
- Status
- Sort order

### Supported Statuses

- Open
- Closed
- Hidden
- Full
- Suspended

### Business Rules

1. Only active tenant programmes can be opened for admission.
2. Closed programmes must not appear on the public application form.
3. Quota enforcement must be available but configurable.
4. Department and programme must belong to the same tenant.
5. Programme requirements must be tenant-configurable.

### Audit Events

- Programme opened for admission
- Programme removed from admission cycle
- Programme quota changed
- Programme admission status changed
- Programme requirement updated

---

## Module 3: Admission Requirements Configuration

### Purpose

To support SHST-specific entry requirements without hard-coding them.

### Required Capabilities

Authorized users must be able to configure:

- Required O'Level subjects
- Minimum grade per subject
- Maximum number of sittings
- Required documents
- Required applicant biodata fields
- Age rule where applicable
- JAMB/UTME requirement where applicable
- Professional requirement notes
- Screening/interview requirement
- Medical fitness requirement placeholder
- Indigene certificate requirement placeholder

### Nigeria-Aware Requirement Support

The system must support common Nigerian exam types:

- WAEC
- NECO
- NABTEB
- GCE
- Custom tenant-defined exam type

The system must support common O'Level grades:

- A1
- B2
- B3
- C4
- C5
- C6
- D7
- E8
- F9
- AR
- Awaiting Result

### Required Fields

Admission requirement should support:

- Tenant ID
- Admission cycle ID
- Programme ID
- Requirement title
- Subject requirements
- Minimum grades
- Number of sittings allowed
- Required documents
- Additional notes
- Status

### Business Rules

1. Requirements must be configurable per programme.
2. Requirement rules must not be hard-coded inside validation logic.
3. The system may assist with eligibility checking, but final admission decision remains controlled by authorized school officers.
4. Awaiting result must be supported where tenant allows it.

### Audit Events

- Admission requirement created
- Admission requirement updated
- Required subject changed
- Required document changed
- Eligibility rule changed

---

## Module 4: Applicant Access and Identity

### Purpose

To allow applicants to create and access application records using Nigeria-friendly login identifiers.

### Required Capabilities

Applicant must be able to:

- Start application from tenant public website
- Create applicant access profile
- Use phone number or email
- Verify access where implemented
- Continue saved application
- Submit final application
- Track status

### Identity Rule

Applicant identity shall use the same foundation decision:

- Email address login supported
- Nigerian phone number login supported
- Phone numbers normalized to `+234...`
- Email normalized to lowercase

### Applicant Account Decision

Applicant access must be tenant-aware.

An applicant may apply to a tenant admission cycle. If the same person applies to another tenant, the system must prevent data mixing while still allowing identity reuse where the authentication foundation supports it.

### Required Applicant Statuses

- Started
- Draft
- Submitted
- Under Review
- Shortlisted
- Offered Admission
- Rejected
- Waitlisted
- Accepted
- Cleared
- Converted to Student
- Withdrawn

### Business Rules

1. Applicants must only see their own applications.
2. Applicants must not access tenant admin screens.
3. Applicant sessions must resolve tenant context.
4. Applicant dashboard must be simple and mobile-first.
5. Applicant should be able to save draft before final submission.

### Audit Events

- Applicant account created
- Applicant login
- Applicant application started
- Applicant application resumed
- Applicant submitted application
- Applicant accepted admission

---

## Module 5: Applicant Biodata Capture

### Purpose

To collect applicant personal information in a tenant-configurable but structured manner.

### Required Capabilities

Applicant form must support:

- Surname
- First name
- Other names
- Gender
- Date of birth
- Phone number
- Email address
- Residential address
- State of origin
- LGA of origin
- Nationality
- Marital status where required
- Religion where required by tenant
- Next of kin details
- Guardian/sponsor details
- Passport photograph

### Nigeria-Aware Data Rules

1. Phone number must accept Nigerian formats and normalize internally.
2. State and LGA should use Nigerian reference data.
3. Names must support local spellings.
4. Passport upload must use the file/media foundation.
5. Long forms must be step-based on mobile.

### Required Fields Must Be Configurable

The tenant should be able to decide which fields are:

- Required
- Optional
- Hidden
- Read-only after submission

### Business Rules

1. Applicant biodata must be tenant-scoped.
2. Applicant biodata must be locked after final submission, except where tenant allows correction.
3. Corrections after submission must be audited.
4. Applicant passport must be stored as protected tenant media.

### Audit Events

- Applicant biodata saved
- Applicant biodata submitted
- Applicant biodata corrected after submission
- Applicant passport uploaded
- Applicant passport changed

---

## Module 6: O'Level Result Capture

### Purpose

To support Nigerian SHST admission screening based on WAEC/NECO/NABTEB/GCE results.

### Required Capabilities

Applicant must be able to enter one or more sittings.

Each sitting should capture:

- Exam type
- Exam year
- Exam number
- School/center name where required
- Subject
- Grade

### Required Exam Types

Default editable exam types:

- WAEC
- NECO
- NABTEB
- GCE
- Awaiting Result
- Custom

### Required Subject Support

The system must support common subjects such as:

- English Language
- Mathematics
- Biology
- Chemistry
- Physics
- Health Science
- Agricultural Science
- Economics
- Geography
- Civic Education
- Other tenant-defined subjects

### Business Rules

1. Tenant programme requirement defines required subjects.
2. Maximum sitting count must follow tenant programme rule.
3. Awaiting result must only be allowed if tenant permits it.
4. Eligibility check must flag issues but must not silently reject applicant unless configured.
5. Admission officer must be able to review O'Level entries.

### Audit Events

- O'Level sitting added
- O'Level sitting updated
- O'Level subject grade changed
- O'Level document uploaded
- O'Level eligibility checked

---

## Module 7: Application Document Upload

### Purpose

To allow applicants to upload required admission documents safely.

### Required Capabilities

Applicant must be able to upload:

- Passport photograph
- O'Level result
- Birth certificate or declaration of age
- Indigene certificate, where required
- JAMB/UTME evidence, where required
- Payment evidence placeholder, where manual payment is enabled
- Other tenant-defined documents

### File Storage Rule

Every uploaded document must use the file/media foundation and store:

- Tenant ID
- Applicant ID
- Application ID
- File category
- Original filename
- Stored filename
- MIME type
- Size
- Uploaded by
- Visibility level
- Status

### Document Statuses

- Uploaded
- Pending Review
- Accepted
- Rejected
- Replacement Requested

### Business Rules

1. Uploaded files must not be publicly accessible by direct path.
2. Applicant can only view own documents.
3. Admission officers can view documents only within tenant and authority scope.
4. Rejected document should allow replacement if tenant permits.

### Audit Events

- Document uploaded
- Document replaced
- Document accepted
- Document rejected
- Document downloaded/viewed by admission officer

---

## Module 8: Application Submission

### Purpose

To allow applicant to complete and submit application for review.

### Required Capabilities

System must:

- Validate required biodata
- Validate programme selection
- Validate O'Level entries
- Validate required documents
- Check payment checkpoint status if configured
- Generate application number
- Lock submitted application
- Show submission confirmation
- Notify applicant where notification channel exists

### Required Application Number Rule

Application number must be tenant-configurable.

Example patterns:

- `APP/2026/0001`
- `SHST/APP/2026/0001`
- `CHT/ADM/2026/0001`

### Submission Rules

1. Draft applications may be edited.
2. Submitted applications may not be edited except through correction workflow.
3. Application cannot be submitted after cycle closes.
4. Applicant can submit only to open programmes.
5. Duplicate application control must be configurable.

### Duplicate Control Options

Tenant may configure duplicate check using:

- Phone number
- Email
- Full name + date of birth
- O'Level exam number
- Programme + admission cycle

### Audit Events

- Application submitted
- Application number generated
- Submission failed validation
- Duplicate application detected
- Application locked after submission

---

## Module 9: Admission Review Workspace

### Purpose

To allow admission officers and authorized staff to review submitted applications.

### Required Capabilities

Admission officers must be able to:

- View applicant list
- Filter by admission cycle
- Filter by programme
- Filter by department
- Filter by status
- Search applicant
- View applicant details
- View O'Level results
- View uploaded documents
- Add internal review comments
- Mark application as under review
- Flag incomplete application
- Mark screening status
- Shortlist applicant
- Reject applicant
- Waitlist applicant
- Recommend admission

### Required Filters

- Admission cycle
- Programme
- Department
- Application status
- Screening status
- Payment checkpoint status
- Date submitted
- Gender
- State/LGA where required
- Document status

### Business Rules

1. Admission officer must only view applications in active tenant.
2. Department-scoped reviewer must only view assigned department/programme applications.
3. Review comments must be internal and not visible to applicant unless explicitly published.
4. Sensitive decisions must be audited.

### Audit Events

- Application viewed by officer
- Application marked under review
- Review comment added
- Application flagged incomplete
- Screening status changed
- Applicant shortlisted
- Applicant waitlisted
- Applicant rejected
- Applicant recommended for admission

---

## Module 10: Screening and Shortlisting

### Purpose

To support screening decisions before final admission offer.

### Required Capabilities

System must support:

- Screening status update
- Screening score entry where applicable
- Interview status
- Document verification status
- Eligibility flag
- Shortlist generation
- Shortlist approval placeholder

### Screening Statuses

- Not Screened
- Pending Screening
- Eligible
- Not Eligible
- Invited for Interview
- Interviewed
- Recommended
- Not Recommended

### Business Rules

1. Screening method is tenant-configurable.
2. Some tenants may screen by document review only.
3. Some tenants may use entrance exam/interview.
4. System must not force one screening model.
5. Screening result must be auditable.

### Audit Events

- Screening status changed
- Screening score entered
- Interview status changed
- Applicant shortlisted
- Applicant removed from shortlist

---

## Module 11: Admission Offer Management

### Purpose

To allow authorized users to offer admission to selected applicants.

### Required Capabilities

Authorized users must be able to:

- Generate proposed admission list
- Review proposed list
- Offer admission
- Reject application
- Waitlist application
- Assign offered programme
- Change offered programme where permitted
- Generate admission letter
- Publish admission offer to applicant

### Admission Decision Statuses

- Pending Decision
- Recommended
- Offered
- Rejected
- Waitlisted
- Deferred
- Offer Withdrawn

### Required Admission Offer Fields

- Tenant ID
- Application ID
- Applicant ID
- Admission cycle ID
- Offered programme ID
- Offered department ID
- Offered level ID
- Offer status
- Offer date
- Offer expiry date, where applicable
- Offered by
- Approval status
- Admission letter template ID, where applicable

### Business Rules

1. Admission offer must be issued only for tenant application.
2. Applicant must be in a valid status before offer.
3. Programme quota must be considered where enabled.
4. Changing offered programme must be audited.
5. Admission letter content must be tenant-configurable.
6. Admission offer does not automatically create student record.

### Audit Events

- Admission offered
- Offer changed
- Offer withdrawn
- Applicant rejected
- Applicant waitlisted
- Admission letter generated
- Admission offer published

---

## Module 12: Admission List Publication

### Purpose

To allow schools to publish approved admission lists on their tenant public website or applicant portal.

### Required Capabilities

Authorized users must be able to:

- Generate admission list
- Preview admission list
- Approve publication
- Publish list
- Unpublish list where permitted
- Show list publicly or only to applicants
- Filter published list by programme/department

### Publication Modes

The system must support:

- Applicant portal only
- Public website list
- PDF export
- Internal list only

### Business Rules

1. Published list must be tenant-specific.
2. Public list must not expose sensitive applicant data.
3. Public list may show application number, name, programme, and status based on tenant setting.
4. Publication must be audited.
5. Unpublishing must be audited.

### Audit Events

- Admission list generated
- Admission list approved
- Admission list published
- Admission list unpublished
- Admission list exported

---

## Module 13: Admission Acceptance

### Purpose

To allow offered applicants to accept admission.

### Required Capabilities

Applicant must be able to:

- View admission offer
- Download/view admission letter
- Accept admission
- Decline admission, where enabled
- View acceptance instructions
- See acceptance fee checkpoint where configured
- Track clearance status

### Acceptance Statuses

- Pending
- Accepted
- Declined
- Expired
- Cancelled

### Business Rules

1. Only offered applicants can accept admission.
2. Acceptance deadline may be configured per admission cycle.
3. Acceptance may require payment checkpoint from Block 4 when available.
4. Acceptance does not automatically mean clearance.
5. Accepted applicants are not yet full students until conversion in Block 5.

### Audit Events

- Admission viewed by applicant
- Admission accepted
- Admission declined
- Acceptance deadline expired
- Acceptance status changed by officer

---

## Module 14: Clearance Status Placeholder

### Purpose

To prepare accepted applicants for eventual student record creation.

### Required Capabilities

Authorized users must be able to mark clearance status.

### Clearance Statuses

- Not Started
- Pending
- In Progress
- Cleared
- Not Cleared
- Correction Required

### Clearance Data in Block 3

Block 3 only supports admission clearance status.

Full student clearance workflows belong to later student services/graduation blocks.

### Business Rules

1. Clearance status must be tenant-scoped.
2. Clearance decision must be audited.
3. Only accepted applicants can be cleared.
4. Cleared applicants become eligible for conversion into student records in Block 5.
5. Block 3 must not build full student profile management.

### Audit Events

- Clearance started
- Applicant marked cleared
- Applicant marked not cleared
- Clearance correction requested
- Clearance status changed

---

# 7. Required Data Ownership Rules

## 7.1 Tenant-Owned Data

The following are tenant-owned and must include `tenant_id`:

- Admission cycles
- Admission programme openings
- Admission requirements
- Applicants
- Applications
- Applicant biodata
- Applicant O'Level results
- Applicant documents
- Application reviews
- Screening records
- Shortlists
- Admission offers
- Admission lists
- Acceptance records
- Clearance statuses
- Admission notifications
- Admission audit records

## 7.2 Platform-Owned Data

Platform-owned data may include:

- Default exam type templates
- Default O'Level grade templates
- Default subject templates
- Default admission status codes
- Platform-wide application reference pattern templates

## 7.3 Shared Reference Data

Shared reference data may include:

- Nigerian states
- Nigerian LGAs
- Default gender options
- Default marital status options
- Default exam types
- Default O'Level subjects
- Default O'Level grades

## 7.4 Tenant-Configured Reference Data

Tenant-configured data includes:

- Admission cycle names
- Programme admission requirements
- Required documents
- Screening methods
- Application form required fields
- Admission letter template
- Admission list visibility
- Acceptance deadline
- Clearance requirements

---

# 8. Required Services

Block 3 must include service-layer logic for:

1. AdmissionCycleService
2. AdmissionProgrammeService
3. AdmissionRequirementService
4. ApplicantIdentityService
5. ApplicantProfileService
6. OLevelResultService
7. ApplicationDocumentService
8. ApplicationSubmissionService
9. ApplicationReviewService
10. ScreeningService
11. ShortlistService
12. AdmissionOfferService
13. AdmissionListPublicationService
14. AdmissionAcceptanceService
15. AdmissionClearanceStatusService
16. AdmissionNotificationService
17. AdmissionReportService
18. AdmissionAuditService

Controllers must call services. Controllers must not contain business logic.

---

# 9. Required Controllers

## Public/Applicant Controllers

- PublicAdmissionController
- ApplicantAuthController
- ApplicantDashboardController
- ApplicantApplicationController
- ApplicantOLevelController
- ApplicantDocumentController
- ApplicantOfferController

## Tenant Admin Controllers

- AdmissionCycleController
- AdmissionProgrammeController
- AdmissionRequirementController
- AdmissionApplicationReviewController
- AdmissionScreeningController
- AdmissionOfferController
- AdmissionListController
- AdmissionClearanceController
- AdmissionReportController

## API Controllers

- AdmissionCycleApiController
- ApplicantApplicationApiController
- OLevelApiController
- ApplicantDocumentApiController
- AdmissionReviewApiController
- AdmissionOfferApiController
- AdmissionListApiController

---

# 10. Required Models

Tenant-scoped models must extend the tenant-scoped base model from Block 1.

Required models include:

- AdmissionCycleModel
- AdmissionProgrammeModel
- AdmissionRequirementModel
- ApplicantModel
- ApplicationModel
- ApplicantBiodataModel
- ApplicantOLevelSittingModel
- ApplicantOLevelSubjectModel
- ApplicantDocumentModel
- ApplicationReviewModel
- ScreeningRecordModel
- AdmissionShortlistModel
- AdmissionOfferModel
- AdmissionListModel
- AdmissionAcceptanceModel
- AdmissionClearanceStatusModel
- AdmissionNotificationModel

---

# 11. Required Views and Pages

## Public Website Pages

- Admission landing page
- Available programmes for application
- Admission requirements page
- Application start page
- Admission list public page, where enabled

## Applicant Pages

- Applicant login/register
- Applicant dashboard
- Application form step 1: programme selection
- Application form step 2: biodata
- Application form step 3: O'Level results
- Application form step 4: document upload
- Application form step 5: preview and submit
- Application status page
- Admission offer page
- Admission acceptance page

## Tenant Admin Pages

- Admission cycles list
- Create/edit admission cycle
- Programmes open for admission
- Admission requirements setup
- Applications list
- Application review detail
- Screening workspace
- Shortlist workspace
- Admission offer workspace
- Admission list publication page
- Acceptance tracking page
- Clearance tracking page
- Admission reports page

---

# 12. Vue Composition API Usage

Vue Composition API should be used for:

- Multi-step application form
- Programme selection dependent on admission cycle
- O'Level sitting and subject entry
- Dynamic document upload checklist
- Application preview before submission
- Admission officer filters
- Screening status updates
- Shortlist table interactions
- Admission offer batch actions
- Admission list preview
- Applicant status tracking widgets

Vue must not bypass server-side validation or authorization.

Every Vue/API request must enforce:

- Tenant context
- Authentication where required
- Applicant ownership where applicable
- Operational authority where applicable
- CSRF/security policy
- Standard API response format

---

# 13. Standard API Response Format

All Block 3 APIs must return the standard response shape:

```json
{
  "success": true,
  "message": "Operation completed successfully.",
  "data": {},
  "errors": {},
  "meta": {}
}
```

Sensitive actions must include audit reference:

```json
{
  "success": true,
  "message": "Admission offer published successfully.",
  "data": {},
  "errors": {},
  "meta": {},
  "audit_reference": "AUD-ADM-2026-000001"
}
```

---

# 14. Validation Rules

## Admission Cycle Validation

- Admission title is required.
- Academic session is required.
- Opening date is required.
- Closing date is required.
- Closing date must not be earlier than opening date.
- Academic session must belong to active tenant.
- Admission cycle code must be unique within tenant.

## Admission Programme Validation

- Admission cycle is required.
- Programme is required.
- Programme must belong to tenant.
- Programme must be active.
- Quota must be numeric if supplied.
- Entry level must belong to tenant if supplied.

## Applicant Validation

- Surname is required.
- First name is required.
- Phone number or email is required.
- Phone number must be normalized if supplied.
- Email must be valid and lowercase if supplied.
- Date of birth must be valid.
- State/LGA must be valid where required.
- Programme selection must belong to active admission cycle.

## O'Level Validation

- Exam type is required.
- Exam year is required.
- Subjects are required based on programme rule.
- Grades must be valid.
- Number of sittings must not exceed tenant programme rule.
- Awaiting result must be allowed before use.

## Document Validation

- Required documents must be uploaded before final submission.
- File type must be allowed.
- File size must be within limit.
- File must be linked to correct tenant and application.
- File category must match required document type.

## Admission Decision Validation

- Application must be submitted.
- Application must belong to tenant.
- Application must be in valid status for decision.
- Offer programme must belong to tenant.
- Officer must have required authority.
- Decision comment may be required for rejection or withdrawal.

---

# 15. Required Audit Events

Block 3 must audit:

- Admission cycle created
- Admission cycle updated
- Admission cycle opened
- Admission cycle closed
- Programme opened for admission
- Admission requirement created
- Admission requirement updated
- Applicant account created
- Applicant application started
- Applicant biodata saved
- Applicant O'Level result saved
- Applicant document uploaded
- Application submitted
- Application reviewed
- Review comment added
- Screening status changed
- Applicant shortlisted
- Applicant recommended
- Applicant rejected
- Applicant waitlisted
- Admission offered
- Admission offer changed
- Admission offer withdrawn
- Admission letter generated
- Admission list generated
- Admission list published
- Admission list unpublished
- Admission accepted
- Admission declined
- Clearance status changed
- Applicant converted eligibility marked

---

# 16. Required Reports

## Tenant Admission Reports

- Admission cycle summary
- Applicants by programme
- Applicants by department
- Submitted applications
- Draft applications
- Incomplete applications
- Shortlisted applicants
- Offered applicants
- Rejected applicants
- Waitlisted applicants
- Accepted applicants
- Cleared applicants
- Applicants by state
- Applicants by gender
- Applicants by O'Level status
- Admission quota utilization
- Admission audit report

## Report Rules

Every report must include:

- Tenant name
- Report title
- Admission cycle
- Date generated
- Generated by
- Filters applied
- Programme/department where applicable

---

# 17. Required Notifications

Block 3 must prepare notification events for:

- Application started
- Application submitted
- Application correction required
- Screening invitation
- Admission offered
- Admission rejected
- Admission waitlisted
- Admission accepted
- Clearance status changed
- Admission list published

Notification channels may include:

- In-app notification
- Email
- SMS placeholder
- Dashboard notice

SMS integration may be fully implemented later, but events and hooks must exist.

---

# 18. Access Control Matrix

## Applicant

Can:

- Start application
- Save own draft
- Submit own application
- Upload own documents
- View own status
- View own offer
- Accept own admission

Cannot:

- View another applicant's data
- View tenant admin area
- Change submitted application unless correction is allowed
- Change admission decision

## Admission Officer

Can:

- View applications within assigned scope
- Review applications
- Add review comments
- Update screening status
- Shortlist applicants
- Recommend admission

Cannot:

- Access another tenant
- Publish final admission list unless authorized
- Override payment checkpoint unless authorized

## Registrar

Can:

- Review admission records
- Approve admission processes where configured
- View admission reports
- Manage admission list workflow where authorized

Cannot:

- Access another tenant
- Modify platform tenant settings

## Tenant Admin

Can:

- Configure admission cycle
- Configure programme openings
- Configure requirements
- Manage admission workflow based on authority
- View admission reports

Cannot:

- Access another tenant
- Bypass audit
- Perform platform admin actions

## Tenant Super Admin

Can:

- Manage admission configuration
- Assign admission authorities
- View full tenant admission records
- Override tenant-level admission settings where authorized

Cannot:

- Access another tenant
- Modify platform-owned data

## Platform Admin

Can:

- View tenant admission module status
- Support tenant troubleshooting in controlled/audited mode

Cannot:

- Casually alter admission decisions without support workflow and audit
- Access applicant data without controlled platform-admin context

---

# 19. Dashboards and Workspace Requirements

## Applicant Dashboard

Must show:

- Tenant branding
- Current application status
- Selected programme
- Missing application steps
- Submission status
- Admission decision status
- Acceptance action if offered
- Clearance status after acceptance

## Admission Officer Dashboard

Must show:

- Total submitted applications
- Pending review count
- Incomplete applications
- Shortlisted applicants
- Offered applicants
- Accepted applicants
- Programme filters
- Quick access to review workspace

## Tenant Admin Admission Dashboard

Must show:

- Active admission cycle
- Open programmes
- Application count
- Programme quota utilization
- Admission list status
- Acceptance summary
- Clearance summary
- Setup gaps

---

# 20. Mobile-First Requirements

Applicant-facing pages must be optimized for mobile because many applicants will apply using phones.

## Mandatory Mobile Rules

- Application form must be step-based.
- Each step must save draft.
- Input fields must be touch-friendly.
- File upload instructions must be clear.
- O'Level entry must be simple and repeatable.
- Long tables must become cards on mobile.
- Admission status must be visible immediately after login.
- Error messages must be simple.

## Nigeria-Aware Mobile Considerations

The system must assume:

- Unstable network
- Low-end Android devices
- Users may not check email frequently
- Phone number may be the primary identity
- Applicants may need to pause and continue later
- Uploads may fail and need retry

---

# 21. Implementation Order for Block 3

Developers must implement Block 3 in this order:

## Step 1: Admission Foundation

- Create admission cycle structure
- Create admission programme opening structure
- Create admission requirement structure
- Add tenant-scoped models
- Add services
- Add audit events

## Step 2: Applicant Access Foundation

- Implement applicant start flow
- Implement applicant login/register flow using phone/email
- Implement applicant dashboard
- Ensure tenant context is resolved from public website entry

## Step 3: Application Form

- Programme selection
- Biodata capture
- O'Level capture
- Document upload
- Preview and submit
- Application number generation
- Draft/save logic

## Step 4: Admission Admin Workspace

- Applications list
- Filters/search
- Application detail view
- Review comments
- Document review
- Screening status

## Step 5: Shortlisting and Decision

- Shortlist workflow
- Recommendation workflow
- Offer admission
- Reject/waitlist
- Admission letter template hook
- Admission decision audit

## Step 6: Admission List Publication

- Generate admission list
- Preview list
- Publish list
- Public/applicant portal visibility
- PDF/export support where appropriate

## Step 7: Acceptance and Clearance Status

- Applicant offer view
- Accept/decline offer
- Acceptance status tracking
- Clearance status placeholder
- Eligible-for-student-conversion marker

## Step 8: Reports and Notifications

- Admission reports
- Notification events
- Admission dashboard summaries
- Audit report

## Step 9: Final Stabilization

- Tenant isolation tests
- Applicant ownership tests
- Mobile tests
- Authorization tests
- Admission lifecycle tests
- Audit tests

---

# 22. Testing Checklist

## Tenant Isolation Tests

- Tenant A applicants are invisible to Tenant B.
- Tenant A admission cycles are invisible to Tenant B.
- Tenant A programme openings are invisible to Tenant B.
- Tenant A admission list cannot show Tenant B applicants.
- Direct URL ID manipulation does not expose another tenant's application.
- Uploaded applicant documents cannot be accessed across tenants.

## Applicant Ownership Tests

- Applicant can view only own application.
- Applicant cannot view another applicant's offer.
- Applicant cannot edit application after submission unless correction is allowed.
- Applicant cannot submit to a closed admission cycle.
- Applicant cannot apply to a hidden/closed programme.

## Admission Officer Tests

- Admission officer can review applications within scope.
- Department-scoped officer cannot review outside department.
- Officer cannot publish admission list without authority.
- Officer actions are audited.

## Admission Lifecycle Tests

- Draft application can be saved.
- Submitted application is locked.
- Under review status works.
- Shortlist works.
- Offer works.
- Rejection works.
- Waitlist works.
- Acceptance works.
- Clearance status works.
- Converted-to-student eligibility marker works.

## Validation Tests

- Invalid Nigerian phone number is rejected.
- Valid Nigerian phone number is normalized.
- Required biodata fields are enforced.
- Required O'Level subjects are checked.
- Required documents are enforced.
- Invalid file type is rejected.
- Duplicate application rule works.

## Mobile Tests

- Application form is usable on mobile.
- O'Level entry is usable on mobile.
- Upload interface is usable on mobile.
- Applicant dashboard is readable.
- Admission status is clear.
- Admin review pages do not break on mobile.

## Audit Tests

- Application submission is audited.
- Screening update is audited.
- Admission offer is audited.
- Admission rejection is audited.
- Admission list publication is audited.
- Admission acceptance is audited.
- Clearance status change is audited.

---

# 23. Acceptance Criteria

Block 3 is complete only when:

1. Tenant can create an admission cycle.
2. Tenant can open selected programmes for application.
3. Tenant can configure programme-specific admission requirements.
4. Public website can show available admission programmes.
5. Applicant can start application from tenant public website.
6. Applicant can login or continue application using email or Nigerian phone number.
7. Applicant can complete biodata, O'Level, documents, preview, and submit.
8. System generates tenant-scoped application number.
9. Admission officer can review submitted applications.
10. Screening and shortlisting workflow works.
11. Authorized user can offer, reject, or waitlist applicant.
12. Admission list can be generated and published based on tenant setting.
13. Applicant can view and accept admission offer.
14. Clearance status can be tracked.
15. Accepted and cleared applicant can be marked eligible for Block 5 student conversion.
16. All sensitive actions are audited.
17. Tenant isolation is enforced across all admission data.
18. Applicant-facing pages are mobile-first and usable.
19. No tenant-specific admission data is hard-coded.
20. Full payment engine is not implemented inside this block.

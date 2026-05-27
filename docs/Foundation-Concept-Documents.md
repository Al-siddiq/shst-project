# Foundation-Concept Documents

## SaaS Multi-Tenant SHST Information Management System

### Developer-Centered Architectural Reference

---

## Document Status

**Project Type:** SaaS multi-tenant web application
**Target Sector:** Nigerian Schools of Health Sciences and Technology
**Framework Decision:** CodeIgniter 4
**Authentication Foundation:** CodeIgniter Shield
**Frontend Enhancement:** Vue.js Composition API
**Design Priority:** Mobile-first, Nigeria-aware, tenant-configurable
**Primary Goal:** Build one flexible SHST SaaS platform that any institution can configure and use without custom development.

CodeIgniter Shield is the official authentication and authorization framework for CodeIgniter 4, and it is designed to be flexible and extendable. This project will use Shield as the identity foundation while adding tenant-aware operational authority above it. ([codeigniter.com][1])

---

# 1. Master Architectural Decision

## Decision

The system shall be built as a **single SaaS platform with shared application code and shared database schema**, where every tenant-owned record is isolated by `tenant_id`.

Each School of Health Sciences and Technology is a **tenant**.

Each tenant configures its own:

* School profile
* Departments
* Programmes
* Courses
* Academic sessions
* Semesters
* Levels
* Fees
* Admission settings
* Staff records
* Student records
* Result rules
* Website content
* Theme colors
* Department color codes
* Operational roles and approval authorities

## Mandatory Rule

No tenant-specific school data shall be hard-coded in controllers, views, Vue components, helpers, seeders, config files, or route files.

Any data displayed to a tenant user must come from the database if it belongs to that tenant.

---

# 2. Foundation Concept One: Tenancy Model

## Objective

To guarantee that each school experiences the system as its own independent portal while sharing the same SaaS platform.

## Tenancy Decision

The platform shall use **shared-schema multi-tenancy**.

That means:

* One application codebase
* One primary database
* Shared tables
* Tenant-owned data separated by `tenant_id`
* Platform-level data separated from tenant-level data

## Tenant Resolution

Every request must resolve tenant context before tenant-owned data is accessed.

Tenant context may be resolved through:

1. Subdomain
   Example: `schoola.platform.com`

2. Custom domain
   Example: `portal.schoola.edu.ng`

3. Tenant slug in URL, only where needed
   Example: `/t/schoola/login`

4. Authenticated user tenant membership

## Tenant Resolution Priority

Tenant resolution shall follow this order:

1. Custom domain
2. Subdomain
3. Explicit tenant slug
4. Authenticated user’s active tenant
5. Reject request if tenant context is required but missing

## Tenant Isolation Rules

Every tenant-owned table must include:

* `tenant_id`
* `created_by`
* `updated_by`
* `deleted_at`, where soft delete is required
* `created_at`
* `updated_at`

Every tenant-owned query must be tenant-scoped.

This rule applies to:

* Students
* Staff
* Applicants
* Departments
* Programmes
* Courses
* Fees
* Payments
* Results
* Website content
* News
* Announcements
* Course registrations
* Course allocations
* Files
* Audit logs related to tenant activity

## Cross-Tenant Protection

The following must never happen:

* A tenant admin seeing another school’s students
* A lecturer seeing another school’s courses
* A student viewing another school’s fees or result
* A public website showing another school’s content
* A file uploaded by one school being accessible to another school
* A report mixing tenant data unless viewed by platform admin in a controlled platform report

## Platform Admin Exception

Platform admins may access tenant data only through controlled platform-admin interfaces.

Every platform-admin access into tenant data must be audited.

The audit trail must clearly show:

* Which platform admin accessed the tenant
* Which tenant was accessed
* What action was performed
* Date and time
* IP address
* User agent
* Reason/context where applicable

---

# 3. Foundation Concept Two: Database and Data Ownership

## Objective

To make the database flexible enough for a SaaS SHST system while preventing future refactoring.

## Database Decision

The database shall be organized into four data ownership categories.

| Category                         | Meaning                  | Example                                 |
| -------------------------------- | ------------------------ | --------------------------------------- |
| Platform-owned data              | Belongs to SaaS provider | Tenants, subscriptions, platform admins |
| Tenant-owned data                | Belongs to one school    | Students, staff, courses, fees          |
| Shared reference data            | General reusable data    | Nigerian states, LGAs, gender options   |
| Tenant-configured reference data | Configurable per school  | Levels, departments, grading scales     |

## Mandatory Rule

If data can vary from one school to another, it must be tenant-configured in the database.

Do not hard-code:

* Department names
* Programme names
* Course names
* Fee names
* Fee amounts
* Grading scales
* Academic levels
* Semester names
* Admission requirements
* Result remarks
* Website sections
* Approval titles
* Department colors
* School colors

## Internal Codes vs Display Names

The system may use internal stable codes for logic.

Example:

* `active`
* `inactive`
* `pending`
* `approved`
* `rejected`
* `published`
* `draft`

But user-facing display names must be configurable where the term belongs to tenant operations.

Example:

* Internal code: `level_1`
* Tenant display: `Year One`, `ND I`, `HND I`, or `Part One`

## Database Design Rule

Every major table must clearly answer:

1. Is this platform-owned or tenant-owned?
2. If tenant-owned, where is `tenant_id`?
3. Who created the record?
4. Who last updated it?
5. Can it be soft-deleted?
6. Is it part of an approval workflow?
7. Does it need audit logging?
8. Can its display name vary by school?

## Foreign Key Discipline

Tenant-owned relationships must not cross tenants.

For example:

A student in Tenant A must not be linked to:

* Programme in Tenant B
* Department in Tenant B
* Course in Tenant B
* Fee schedule in Tenant B
* Result rule in Tenant B

This must be enforced at model, service, and validation levels.

---

# 4. Foundation Concept Three: CI4 Application Structure

## Objective

To create a disciplined CodeIgniter 4 structure that can support long-term phased development.

CodeIgniter 4 supports standard MVC application structure, routing, filters, models, validation, database migrations, and API response handling, which will be used as the foundation for this platform. ([codeigniter4.github.io][2])

## Structural Decision

The project shall use a **domain-oriented modular structure**, not a random controller-per-page structure.

The application shall be grouped by business domains.

## Primary Domain Areas

The main system domains shall be:

1. Platform
2. Tenancy
3. Identity and Access
4. Institutional Setup
5. Public Website
6. Admissions
7. Fees and Payments
8. Students
9. Staff
10. Academic Structure
11. Course Allocation
12. Course Registration
13. Results
14. Reports
15. Audit
16. Files and Media
17. Notifications

## Layering Rule

Each domain should be separated into:

* Controllers
* Models
* Entities, where useful
* Services
* Validation rules
* Views
* API resources/response formatters
* Vue components where applicable

## Controller Rule

Controllers must remain thin.

Controllers should only:

* Receive request
* Validate request
* Resolve user and tenant context
* Call service layer
* Return response or view

Controllers must not contain heavy business logic.

## Service Layer Rule

Business logic must live in services.

Examples:

* Admission processing
* Fee invoice generation
* Course registration validation
* Result computation
* Result approval
* Tenant onboarding
* Sidebar content resolution
* Theme resolution

## Model Rule

Models handle database access and tenant-scoped queries.

Tenant-owned models must extend a tenant-aware base model.

## View Rule

Views should not perform business decisions.

Views should only render data already prepared by the controller, service, or view composer.

---

# 5. Foundation Concept Four: Authentication and Login Identity

## Objective

To provide secure login using CodeIgniter Shield while supporting Nigerian user realities.

## Authentication Decision

CodeIgniter Shield shall be used as the authentication foundation.

The system shall support login using:

* Email address
* Phone number

Shield supports customization of the login identifier, including specifying a valid field other than the default identifier. This aligns with the project requirement to support both email and phone-number-based login. ([shield.codeigniter.com][3])

## Nigeria-Aware Login Rule

Phone number login must support Nigerian formats.

The system must normalize phone numbers before authentication.

Accepted user inputs may include:

* `08031234567`
* `8031234567`
* `+2348031234567`
* `2348031234567`

Internally, phone numbers should be stored in normalized international format:

* `+2348031234567`

## Email Rule

Emails must be stored in lowercase normalized form.

Example:

* Input: `MercyFriday5555@GMAIL.COM`
* Stored: `mercyfriday5555@gmail.com`

## Identity Uniqueness Rule

A login identifier must not create confusion across tenants.

The system shall support global user identity with tenant membership.

That means one user account may belong to one or more tenants where necessary, but operational access must always depend on active tenant context.

## Login Flow

The login flow shall be:

1. User enters email address or phone number.
2. System normalizes identifier.
3. Shield authenticates identity.
4. System checks user status.
5. System checks tenant membership.
6. System resolves active tenant.
7. System loads first-layer IAM group.
8. System loads second-layer operational authorities.
9. System redirects user to the correct layout/dashboard.

---

# 6. Foundation Concept Five: Two-Layer Authorization Model

## Objective

To prevent role confusion by separating identity-level access from operational responsibility.

## Authorization Decision

The system shall use a **two-layer authorization model**.

## Layer One: IAM Groups

Layer-one groups are the identity foundation.

These are broad, stable, security-level groups.

Primary IAM groups shall be:

1. Platform Admin
2. Tenant Super Admin
3. Tenant Admin
4. Lecturer
5. Student

Additional layer-one groups may be added only when they represent a true identity class, not a temporary office responsibility.

## Layer Two: Operational Authority

Layer-two authorities control what a user can do inside a tenant.

Examples:

* HOD
* Registrar
* Admission Officer
* Bursar
* Accountant
* Exams Officer
* Course Adviser
* Student Affairs Officer
* ICT Officer
* Departmental Officer
* Result Approver
* Clearance Officer
* Public Website Editor

## Why This Decision Is Final

A person can be a lecturer and also be HOD.

A person can be a tenant admin and also manage admissions.

A person can be a registrar and also approve student records.

Therefore, these operational responsibilities must not all become Shield groups.

Shield groups remain the identity foundation. Operational authority handles runtime responsibilities.

## Authorization Enforcement Rule

Every protected action must pass both checks:

1. **Identity check:** Is the user in the right broad group?
2. **Operational check:** Does the user have authority for this tenant, department, programme, level, or workflow?

CodeIgniter’s security guidance emphasizes authorization checks for administrative functions and API endpoints. This project shall enforce authorization through filters, base controllers, services, and policy checks, not through UI hiding alone. ([codeigniter4.github.io][4])

## Examples

A lecturer may enter result only if:

* User belongs to Lecturer group
* User is a staff member of the tenant
* User is allocated that course
* Result entry window is open
* Result has not been locked
* User has not been removed from the course allocation

A tenant admin may manage students only if:

* User belongs to Tenant Admin or Tenant Super Admin group
* User belongs to the active tenant
* User has student-record authority
* Action is within tenant scope

---

# 7. Foundation Concept Six: Runtime Visibility and Side Navigation Resolver

## Objective

To ensure every user sees only the content, menus, links, actions, and records they are allowed to access.

## Decision

The system shall use a centralized **Side Navigation Content Resolver**.

No layout should hard-code menu items directly.

## Resolver Responsibility

The side navigation resolver must determine menu content based on:

* Active tenant
* Logged-in user
* IAM group
* Operational authorities
* Tenant subscription plan
* Enabled modules
* Department assignment
* Programme assignment
* Student status
* Staff status
* Approval responsibility
* Current academic session
* Feature availability

## Layout Rule

Layouts call the resolver.

Layouts must not decide permissions directly.

## Menu Data Rule

Side navigation items should come from a structured navigation registry, stored or cached from database-backed module definitions.

Each menu item should support:

* Label
* Icon
* Route
* Parent section
* Required IAM group
* Required operational authority
* Required module
* Required tenant status
* Sort order
* Visibility condition
* Active state rule

## Controlled Visibility Rule

Hiding a menu item is not security.

Every route, API endpoint, and action must still enforce authorization.

The side navigation only improves user experience.

Security must happen in filters, policies, services, and tenant-scoped models.

---

# 8. Foundation Concept Seven: Layout and View Architecture

## Objective

To keep the interface consistent, mobile-friendly, and maintainable across all modules.

## Layout Decision

There shall be four primary internal layouts:

1. Platform Admin Layout
2. Tenant Admin Layout
3. Lecturer Layout
4. Student Layout

Tenant Super Admin and Tenant Admin shall use the same base tenant admin layout, with differences controlled by permissions, dashboard widgets, and navigation resolver.

## Layout Structure Rule

Every internal layout, excluding public-facing pages, shall be divided into two primary regions:

1. Left region: Side navigation
2. Right region: Main content

Each region shall have three vertical sections.

## Left Region

The left side navigation shall contain:

1. Top section

   * School logo or platform logo
   * Active tenant identity
   * Collapse/menu toggle

2. Middle section

   * Resolved navigation items
   * Module groups
   * Role-based menu

3. Bottom section

   * User summary
   * Settings link
   * Logout
   * App version/support link where appropriate

## Right Region

The main content area shall contain:

1. Top section

   * Page title
   * Breadcrumb
   * Search/action area
   * Notification indicator
   * Mobile menu trigger

2. Middle section

   * Main page content
   * Forms
   * Tables
   * Dashboards
   * Vue-powered components

3. Bottom section

   * Footer actions
   * Pagination where needed
   * Save/cancel action area
   * Contextual notes

## Public-Facing Layout Exception

Public website pages shall have their own public layout.

The public layout shall be tenant-branded but not use internal dashboard side navigation.

---

# 9. Foundation Concept Eight: Mobile-First UI Policy

## Objective

To make the platform usable for Nigerian users, most of whom will access the system through mobile phones.

## Decision

All internal and public interfaces shall be designed mobile-first.

Desktop design is an enhancement, not the starting point.

## Mandatory Mobile Rules

The UI must support:

* Small screen widths
* Touch-friendly buttons
* Collapsible side navigation
* Clear form sections
* Short labels where possible
* Reduced table overload
* Card-based mobile presentation
* Sticky action buttons for important forms
* Mobile-friendly modals or bottom sheets
* Clear loading states
* Offline/poor-network tolerance where possible

## Nigeria-Aware UI Rules

The system must assume:

* Users may use low-end Android phones
* Network may be slow or unstable
* Users may not be highly technical
* Data cost matters
* Forms must save progressively where appropriate
* Long forms should be broken into steps
* Error messages must be simple and direct
* Phone number input must be friendly to Nigerian formats

## Table Rule

Large tables must not be forced directly onto mobile screens.

For mobile:

* Use cards
* Use filters
* Use search
* Use pagination
* Use “view details” pages
* Hide non-critical columns

For desktop:

* Use full tables where useful

---

# 10. Foundation Concept Nine: Theme, Branding, and Department Color Identity

## Objective

To allow each school to feel like an independent system while also allowing department-level identity.

## Tenant Theme Decision

Each tenant shall configure a general school theme.

Tenant theme should include:

* Primary color
* Secondary color
* Accent color
* Logo
* Favicon
* Public website banner where needed
* Default dashboard appearance

## Department Color Decision

Each department shall have its own color code.

Department color should influence the student experience, especially in:

* Student dashboard
* Course registration pages
* Result pages
* Departmental notices
* Student profile badges
* Department cards
* Programme identity labels

## Theme Resolution Rule

The system shall resolve theme in this order:

1. Platform default theme
2. Tenant school theme
3. Department theme
4. Page/module-specific accent, where applicable

## Student Dashboard Rule

When a student logs in, the dashboard must visually reflect:

* School identity
* Department identity
* Programme identity
* Current level/session where applicable

This must be done without making the UI noisy.

Department colors should appear as accents, highlights, badges, borders, cards, and dashboard headers, not by overwhelming the entire screen.

---

# 11. Foundation Concept Ten: Vue.js and Request Flow

## Objective

To provide a modern, responsive user experience while keeping CodeIgniter 4 as the main application framework.

## Frontend Decision

The system shall use Vue.js Composition API for dynamic interactions and API-driven screens.

CodeIgniter 4 shall remain responsible for:

* Routing
* Authentication
* Authorization
* Server-side rendering where needed
* Tenant resolution
* Core validation
* Business services
* Database operations

Vue shall be used for:

* Dynamic forms
* Dependent dropdowns
* Dashboards
* Filters
* Search
* Course registration interactions
* Payment status refresh
* Result entry tables
* Admission screening actions
* Inline updates
* Notifications
* API-powered widgets

## Page Reload Policy

Full page reload shall be used for:

* Login
* Logout
* Tenant switch
* Major approval submission where server confirmation is critical
* Payment callback handling
* Sensitive result publication actions
* System configuration save where full state refresh is safer

API/Vue interactions shall be used for:

* Form steps
* Searches
* Filters
* Draft saves
* Inline validations
* Dependent selects
* Data tables
* Dashboard summaries
* Non-final workflow actions

## API Response Rule

All API responses must follow one standard shape.

Each response must include:

* `success`
* `message`
* `data`
* `errors`
* `meta`
* `audit_reference`, where applicable

## API Security Rule

Every API endpoint must enforce:

* Authentication
* Tenant context
* Authorization
* CSRF strategy where applicable
* Input validation
* Rate limits for sensitive endpoints
* Audit logging for sensitive actions

---

# 12. Foundation Concept Eleven: Audit Trail System

## Objective

To create a professional audit trail that protects the institution, users, and platform owner.

## Decision

The system shall include a comprehensive audit trail from the beginning.

Audit logging is not a later feature.

## What Must Be Audited

The following actions must be audited:

* Login
* Failed login
* Logout
* Password reset
* Tenant switch
* User creation
* Role/group assignment
* Operational authority assignment
* Student creation/update
* Staff creation/update
* Admission offer
* Admission rejection
* Admission acceptance
* Clearance decision
* Fee configuration
* Payment confirmation
* Course creation/update
* Course allocation
* Course registration
* Course registration approval
* Result entry
* Result update
* Result submission
* Result approval
* Result publication
* Result correction
* Result withholding
* Website content publishing
* File upload/delete
* Tenant settings update
* Theme update
* Department color update
* Platform admin access into tenant data

## Audit Record Must Capture

Each audit log must include:

* Tenant ID, if applicable
* User ID
* User group
* Operational role at time of action
* Action
* Module
* Entity type
* Entity ID
* Old value, where necessary
* New value, where necessary
* IP address
* User agent
* Request method
* Route or endpoint
* Timestamp
* Status: success or failed
* Human-readable summary

## Sensitive Audit Rule

For sensitive modules like payments and results:

* Audit trail must be immutable from normal admin screens
* Audit records must not be deleted by tenant users
* Corrections must create new audit entries, not overwrite history
* Result and payment histories must be traceable

---

# 13. Foundation Concept Twelve: File and Media Management

## Objective

To manage tenant files safely and prevent file leakage across schools.

## Decision

Files must be tenant-scoped.

Uploaded files must not be stored in a way that allows direct uncontrolled public access.

## File Categories

The system shall support:

* School logo
* Public website images
* Student passport
* Applicant passport
* Staff passport
* Admission documents
* O’Level result uploads
* Payment evidence, where manual verification exists
* Result-related files
* Departmental documents
* Accreditation documents
* News images
* Gallery images

## File Storage Rule

Each uploaded file must have a database record containing:

* Tenant ID
* Owner type
* Owner ID
* File category
* Original filename
* Stored filename
* File path
* MIME type
* File size
* Visibility level
* Uploaded by
* Upload date
* Status

## Visibility Levels

Files shall support:

* Public
* Tenant internal
* Department internal
* Staff only
* Student owner only
* Admin only
* Platform only

## File Access Rule

Never trust file path alone.

Every file access must check:

* Tenant
* User identity
* Operational authority
* File visibility
* Ownership where applicable

---

# 14. Foundation Concept Thirteen: Status and Lifecycle Management

## Objective

To avoid scattered status logic across modules.

## Decision

Every major entity shall follow a defined lifecycle.

Status transitions must be controlled.

## Applicant Lifecycle

Standard applicant lifecycle:

1. Draft application
2. Submitted
3. Under review
4. Shortlisted
5. Offered admission
6. Rejected
7. Accepted
8. Cleared
9. Converted to student

## Student Lifecycle

Standard student lifecycle:

1. Active
2. Deferred
3. Suspended
4. Withdrawn
5. Graduated
6. Cleared graduate
7. Archived

## Staff Lifecycle

Standard staff lifecycle:

1. Active
2. On leave
3. Suspended
4. Transferred
5. Retired
6. Resigned
7. Inactive

## Result Lifecycle

Standard result lifecycle:

1. Draft
2. Submitted by lecturer
3. Reviewed by HOD
4. Reviewed by exams officer
5. Approved
6. Published
7. Corrected
8. Locked

## Lifecycle Rule

A user must not manually jump statuses unless the workflow permits it.

Example:

A result cannot move from draft directly to published unless that tenant’s configured approval policy explicitly allows it.

---

# 15. Foundation Concept Fourteen: Configurable Academic Structure

## Objective

To support different SHST academic patterns without custom coding.

## Decision

The system shall provide default SHST templates but allow tenant modification.

## Default Academic Concepts

The platform shall support:

* Sessions
* Semesters
* Levels
* Departments
* Programmes
* Courses
* Course categories
* Course units
* Programme duration
* Admission requirements
* Registration rules
* Result rules

## Level Naming Flexibility

The system must support different level names, including:

* Year 1
* Year 2
* Year 3
* ND I
* ND II
* HND I
* HND II
* Part One
* Part Two
* Certificate Year One

## Programme Type Flexibility

The system must support:

* Certificate
* Diploma
* National Diploma
* Higher National Diploma
* Technician programmes
* Professional health programmes
* Custom school-defined programme types

## Course Type Flexibility

The system must support:

* Compulsory course
* Elective course
* Carryover course
* Practical course
* Clinical/field posting course
* Project course
* Custom tenant-defined course type

---

# 16. Foundation Concept Fifteen: Configurable Fees and Payment Control

## Objective

To support Nigeria-aware school fee operations.

## Decision

Fees must be fully tenant-configurable.

## Supported Fee Types

The platform shall support:

* Application fee
* Acceptance fee
* School fee
* Departmental levy
* Examination fee
* Practical fee
* Lab fee
* Indexing fee
* Hostel fee
* Graduation fee
* Transcript fee
* Certificate fee
* Custom fee

## Fee Applicability

A fee may apply by:

* Tenant
* Session
* Programme
* Department
* Level
* Student category
* New/returning student
* Hostel status
* Admission status
* Custom grouping

## Payment Control Points

A tenant may configure payment requirements for:

* Application submission
* Admission acceptance
* Student clearance
* Course registration
* Examination eligibility
* Result viewing
* Graduation clearance
* Transcript request

## Payment Rule

Payment status must not be treated as a simple yes/no field.

The system must support:

* Fully paid
* Partially paid
* Unpaid
* Waived
* Scholarship-covered
* Manually verified
* Pending confirmation
* Reversed
* Refunded where applicable

---

# 17. Foundation Concept Sixteen: Public Website Data Foundation

## Objective

To make the public-facing website tenant-configurable and database-driven.

## Decision

Each tenant shall manage its own public website content from the system.

## Public Website Content Must Include

* Homepage content
* About content
* Departments
* Programmes
* Admission requirements
* News
* Announcements
* Management profile
* Contact information
* Gallery
* Academic calendar notices
* Portal links

## Public Website Rule

No school public website content shall be hard-coded.

The public website must resolve tenant context and load that school’s data.

## Public Content Visibility

Public content shall support:

* Draft
* Published
* Scheduled
* Archived

---

# 18. Foundation Concept Seventeen: Reporting and Export Foundation

## Objective

To make reporting consistent across modules.

## Decision

Every block must define reports as part of implementation.

Reports must be tenant-scoped by default.

## Report Categories

The system shall support:

* Admission reports
* Applicant reports
* Student population reports
* Staff reports
* Fee reports
* Debtor reports
* Course registration reports
* Course allocation reports
* Result reports
* Department reports
* Programme reports
* Audit reports
* Public website content reports

## Export Rule

Exported reports must include:

* Tenant name
* Report title
* Date generated
* Generated by
* Filters applied
* Academic session where applicable
* Department/programme where applicable

## Report Access Rule

Users can only export reports they are authorized to view.

---

# 19. Foundation Concept Eighteen: Notification Foundation

## Objective

To support communication without depending only on physical notice boards.

## Decision

The system shall support notification infrastructure from the beginning, even if advanced SMS/email integration comes later.

## Notification Channels

The system shall support:

* In-app notifications
* Email notifications
* SMS notifications, where integrated
* Public announcements
* Dashboard notices

## Notification Events

The system should prepare for:

* Application submitted
* Admission offered
* Admission accepted
* Payment confirmed
* Course registration approved
* Result published
* Result correction approved
* Fee deadline approaching
* Student status changed
* Staff assigned to course

## Nigeria-Aware Rule

SMS should be treated as important because some students may not check email regularly.

However, SMS integration may be implemented per block or later phase.

---

# 20. Foundation Concept Nineteen: Validation and Data Quality

## Objective

To prevent dirty records from damaging the platform.

## Decision

Validation must happen at multiple levels.

## Validation Layers

The system shall validate data at:

1. Frontend level
2. API/controller level
3. Service level
4. Model/database level

## Nigeria-Aware Data Fields

The system must properly handle:

* Nigerian phone numbers
* Nigerian states
* LGAs
* Multiple O’Level sittings
* WAEC/NECO/NABTEB result formats
* Names with local spellings
* Gender and marital status where required by school forms
* State of origin
* LGA of origin
* Religion only where institutionally required
* Passport photograph standards

## Data Quality Rule

Important identity records must avoid uncontrolled duplication.

The system should check duplicates using combinations such as:

* Phone number
* Email
* Application number
* Admission number
* Matric number
* Staff number
* Full name + date of birth + programme

---

# 21. Foundation Concept Twenty: Numbering and Reference Codes

## Objective

To ensure every major record has a readable and traceable reference.

## Decision

The system shall support configurable reference generation.

## Reference Types

The platform shall support:

* Application number
* Admission number
* Student number
* Staff number
* Payment invoice number
* Receipt number
* Course registration number
* Result batch number
* Audit reference
* File reference

## Reference Rule

Reference patterns must be tenant-configurable.

Example patterns:

* `APP/2026/0001`
* `SHST/ADM/2026/0012`
* `HIM/ND1/2026/034`
* `PAY/2026/000045`

## Uniqueness Rule

Reference numbers must be unique within the correct scope.

Some references are tenant-wide.

Some are programme-wide.

Some are session-wide.

The scope must be defined clearly per reference type.

---

# 22. Foundation Concept Twenty-One: Approval Workflow Foundation

## Objective

To support controlled institutional decisions without hard-coding approval chains.

## Decision

Approval workflows shall be configurable per tenant and per module.

## Approval Workflow Must Support

* Single-step approval
* Multi-step approval
* Department-level approval
* Exams office approval
* Management approval
* Academic board approval
* Rejection
* Return for correction
* Resubmission
* Final lock

## Approval Modules

Approval workflows may apply to:

* Admission list
* Student clearance
* Course allocation
* Course registration
* Result approval
* Result correction
* Payment waiver
* Website publication
* Graduation clearance

## Approval Rule

Approval status must not simply be a text field.

It must have:

* Current stage
* Current actor
* Previous actor
* Decision
* Comment
* Date
* Audit trail
* Final status

---

# 23. Foundation Concept Twenty-Two: Security Foundation

## Objective

To protect tenant data, user identity, financial records, and academic records.

## Decision

Security must be built into every module from day one.

## Security Rules

The system must enforce:

* Authentication for protected pages
* Tenant resolution before tenant data access
* Authorization before every protected action
* CSRF protection for form submissions
* Input validation
* Output escaping
* File upload restrictions
* Rate limiting for login and sensitive APIs
* Audit logging
* Secure session handling
* Password reset protection
* No direct object reference without tenant check

## Common Risk Areas

Developers must be careful with:

* Result editing
* Payment confirmation
* Tenant switching
* File viewing
* Student profile access
* Staff profile access
* Public website editing
* Admission offer publishing
* Course registration approval

---

# 24. Foundation Concept Twenty-Three: Error Handling and User Feedback

## Objective

To make the system understandable for non-technical Nigerian users while preserving developer traceability.

## Decision

User-facing errors must be simple.

Technical errors must be logged privately.

## Error Message Rule

Do not show raw database, PHP, or stack trace errors to users.

## User Message Style

Messages should be simple:

* “Payment record not found.”
* “You are not allowed to approve this result.”
* “This student does not belong to your school.”
* “Course registration is closed for this session.”
* “Please enter a valid Nigerian phone number.”

## Developer Logging

Logs should capture:

* Error type
* User ID
* Tenant ID
* Request route
* Input summary where safe
* Stack trace
* Timestamp

---

# 25. Foundation Concept Twenty-Four: Block Development Contract

## Objective

To ensure every future development block follows the same architecture.

## Decision

Every block must be implemented using the same foundation rules.

Before any block is developed, its block reference document must define:

1. Purpose
2. Users involved
3. Tenant-owned data
4. Platform-owned data
5. Required permissions
6. Operational authorities
7. Main workflows
8. Status lifecycle
9. Required settings
10. Required reports
11. Required audit logs
12. Required notifications
13. API endpoints
14. Views/pages
15. Vue components
16. Validation rules
17. Edge cases
18. Testing checklist

## Mandatory Block Rule

No block shall bypass:

* Tenant scoping
* Shield authentication
* Two-layer authorization
* Audit trail
* Layout rules
* Mobile-first UI
* Database-driven tenant data
* Side navigation resolver
* Standard API response format

---

# 26. Development Order Decision

## Final Foundation Development Order

The foundations must be developed in this order:

1. CI4 project foundation
2. Shield authentication foundation
3. Tenant model and tenant resolver
4. Tenant-scoped base model and query enforcement
5. User tenant membership
6. Two-layer authorization foundation
7. Layout structure
8. Side navigation resolver
9. Theme and branding resolver
10. Audit trail foundation
11. File/media foundation
12. API response foundation
13. Validation foundation
14. Notification foundation
15. Reporting/export foundation

Only after these foundations are stable should block-specific development begin.

---

# 27. Final Non-Negotiable Rules

These rules apply to the entire project.

1. The system is SaaS, not custom software for one school.

2. Every school is a tenant.

3. Tenant-owned data must always be tenant-scoped.

4. No tenant-specific display data shall be hard-coded.

5. CodeIgniter 4 is the backend framework.

6. CodeIgniter Shield is the authentication foundation.

7. Login must support email address and Nigerian phone number.

8. Authorization must use two layers: IAM group and operational authority.

9. Side navigation must be resolved centrally.

10. Internal layouts must follow the left/right and top/middle/bottom structure.

11. There must be four primary internal layouts: platform admin, tenant admin, lecturer, and student.

12. UI must be mobile-first.

13. Vue Composition API shall be used for dynamic and API-driven interactions.

14. Full reload shall be reserved for critical actions.

15. Every sensitive action must be audited.

16. Tenant branding and department color identity must be database-driven.

17. Public website content must be tenant-configurable.

18. Fees, grading, programmes, courses, levels, sessions, and admission settings must be configurable.

19. Result processing must be protected with strict approval and audit controls.

20. Every future block must obey these foundation documents.

---

# 28. Summary for Developers

This project must be developed as a **Nigeria-aware, mobile-first, configurable SaaS platform for SHST institutions**.

The system must not assume one school’s structure.

It must provide a general SHST operating framework and allow each school to configure its own reality.

The architectural foundation is:

* CodeIgniter 4 backend
* CodeIgniter Shield authentication
* Shared-schema multi-tenancy
* Strict tenant isolation
* Two-layer authorization
* Database-driven tenant configuration
* Mobile-first layouts
* Vue Composition API for dynamic interactions
* Central side navigation resolver
* Tenant and department theming
* Comprehensive audit trail
* Configurable academic, fee, admission, and result rules

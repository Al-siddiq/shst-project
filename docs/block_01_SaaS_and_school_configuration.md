Block 1: SaaS and School Configuration

## Block 1 Purpose

Block 1 establishes the **core SaaS foundation** that allows any School of Health Sciences and Technology to join the platform, configure its own institution, define its academic structure, manage user access, and prepare the system for all later modules.

No admission, payment, result, student, or staff workflow should be developed before this block is stable.

---

# 1. Block 1 Name

## SaaS and School Configuration Block

This block covers:

1. Tenant/school setup
2. Tenant profile configuration
3. Institutional structure configuration
4. Academic session configuration
5. Semester configuration
6. Level/year configuration
7. Department configuration
8. Programme configuration
9. Course configuration
10. User tenant membership
11. Role and authority foundation
12. Tenant theme configuration
13. Department color configuration
14. Side navigation foundation
15. Audit foundation for configuration actions

---

# 2. Block 1 Objective

To build the configurable foundation that allows any SHST institution to start using the platform without custom development.

At the end of this block, a school must be able to:

* Exist as an independent tenant
* Configure its school profile
* Configure departments
* Configure programmes
* Configure levels
* Configure semesters
* Configure academic sessions
* Configure courses
* Configure school theme colors
* Configure department color codes
* Assign tenant users
* Assign broad IAM groups
* Assign operational authorities
* See controlled navigation based on role and authority
* Operate without seeing data from another tenant

---

# 3. Block 1 Strategic Importance

Block 1 is the most important implementation block because every later module depends on it.

Admissions will depend on programmes.

Fees will depend on sessions, levels, programmes, and student categories.

Course registration will depend on courses, levels, semesters, and programmes.

Results will depend on courses, lecturers, grading rules, sessions, and semesters.

Public website will depend on tenant profile, departments, and programmes.

Therefore, Block 1 must be implemented with strict discipline.

---

# 4. Non-Negotiable Rules for Block 1

1. No school-specific data shall be hard-coded.

2. Every tenant-owned record must have `tenant_id`.

3. Every tenant-owned query must be tenant-scoped.

4. Platform admin must manage tenants from platform context.

5. Tenant users must only manage their own school.

6. CodeIgniter Shield must remain the identity foundation.

7. Phone number and email login must both be supported.

8. IAM groups must not be polluted with operational positions.

9. Operational authority must be separate from Shield groups.

10. Department color codes must be database-driven.

11. Tenant theme must be database-driven.

12. Navigation must be resolved centrally, not manually written per layout.

13. Every configuration change must be audited.

14. UI must be mobile-first.

15. Vue Composition API may be used for dynamic configuration screens.

---

# 5. Block 1 Scope

## 5.1 In Scope

### Platform-Level Features

* Platform admin login
* Tenant creation
* Tenant activation/deactivation
* Tenant profile management
* Tenant domain/subdomain identity
* Tenant subscription/status placeholder
* Tenant settings baseline

### Tenant-Level Features

* School profile setup
* School theme setup
* Department setup
* Department color setup
* Programme setup
* Level setup
* Semester setup
* Academic session setup
* Course setup
* Course-to-programme mapping
* Tenant user membership
* User group assignment
* Operational authority assignment
* Controlled side navigation

### Foundational System Features

* Tenant resolver
* Tenant-scoped base model
* Tenant-aware service pattern
* Tenant-aware validation
* Tenant-aware audit logging
* Standard API response format
* Standard internal layout structure
* Platform admin layout
* Tenant admin layout
* Lecturer layout placeholder
* Student layout placeholder

---

## 5.2 Out of Scope

The following must not be implemented fully in Block 1:

* Student application forms
* Admission screening
* Admission offer
* Fee payment
* Student profile management
* Staff profile management
* Course registration
* Course allocation
* Result entry
* Result computation
* Result approval
* Public website CMS content
* Transcript
* Clearance
* Hostel
* Library
* Professional council reporting

However, Block 1 must create the structure that these later blocks will depend on.

---

# 6. Core Modules Inside Block 1

---

## Module 1: Platform Tenant Management

### Purpose

To allow the SaaS platform owner to create and manage schools as tenants.

### Required Capabilities

Platform admin must be able to:

* Create tenant
* View tenant list
* View tenant details
* Activate tenant
* Suspend tenant
* Deactivate tenant
* Assign tenant slug
* Assign tenant subdomain
* Register tenant custom domain placeholder
* Configure tenant status
* Access tenant overview in controlled mode

### Tenant Statuses

The system must support:

* Pending setup
* Active
* Suspended
* Deactivated
* Archived

### Required Fields

Tenant record should support:

* School name
* Short name
* Slug
* Official email
* Official phone
* Address
* State
* LGA
* Ownership type
* Logo reference
* Primary color
* Secondary color
* Accent color
* Tenant status
* Subscription status placeholder
* Created by
* Updated by

### Audit Requirements

Audit the following:

* Tenant created
* Tenant updated
* Tenant activated
* Tenant suspended
* Tenant deactivated
* Tenant domain changed
* Tenant theme changed
* Platform admin accessed tenant context

---

## Module 2: Tenant Profile Configuration

### Purpose

To allow each school to configure its institutional identity.

### Required Capabilities

Tenant super admin must be able to configure:

* School name
* Short name
* Motto
* Logo
* Favicon
* Address
* State
* LGA
* Contact email
* Contact phone numbers
* Ownership type
* Institution type
* Management title preference
* Academic naming preference

### Important Rule

All school identity data displayed inside the system must come from the tenant profile.

No layout should contain hard-coded school name, logo, address, motto, or color.

### Audit Requirements

Audit:

* School profile update
* Logo update
* Contact update
* Theme update
* Institutional identity update

---

## Module 3: Academic Session Configuration

### Purpose

To allow each tenant to define academic sessions.

### Required Capabilities

Tenant admin must be able to:

* Create academic session
* Set session name
* Set start date
* Set end date
* Mark current session
* Open/close session
* Archive old session
* Prevent duplicate active current session

### Example Session Names

The system must support names like:

* `2025/2026`
* `2026/2027`
* `2026 Academic Session`

### Required Statuses

* Draft
* Open
* Current
* Closed
* Archived

### Business Rule

Only one academic session can be marked as current per tenant at a time.

### Audit Requirements

Audit:

* Session created
* Session updated
* Session opened
* Session marked as current
* Session closed
* Session archived

---

## Module 4: Semester Configuration

### Purpose

To allow schools to configure semester or term structure.

### Required Capabilities

Tenant admin must be able to:

* Create semester
* Rename semester
* Set semester order
* Activate/deactivate semester
* Link semester to academic session where required

### Default Templates

The system may ship with editable defaults:

* First Semester
* Second Semester

But tenants must be able to rename or disable them.

### Business Rule

Semester display names are tenant-controlled.

The system must not assume every school uses the same semester naming pattern.

### Audit Requirements

Audit:

* Semester created
* Semester updated
* Semester activated
* Semester deactivated

---

## Module 5: Level/Year Configuration

### Purpose

To allow each school to define academic levels according to its programme structure.

### Required Capabilities

Tenant admin must be able to:

* Create level
* Rename level
* Set level order
* Link levels to programme types or programmes
* Activate/deactivate level

### Supported Examples

The system must support:

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

### Business Rule

Levels must be configurable because SHST institutions may use different naming conventions.

### Audit Requirements

Audit:

* Level created
* Level updated
* Level activated
* Level deactivated

---

## Module 6: Department Configuration

### Purpose

To allow schools to configure departments and department color identities.

### Required Capabilities

Tenant admin must be able to:

* Create department
* Edit department
* Assign department code
* Assign department color
* Assign department status
* Assign HOD placeholder, where applicable
* View department list
* Deactivate department

### Required Fields

Department should support:

* Tenant ID
* Department name
* Department code
* Description
* Department color
* Status
* Sort order

### Department Color Rule

Every department must support a color code.

The color code will later influence:

* Student dashboard
* Department badges
* Course registration pages
* Result pages
* Department notices
* Programme labels

### Business Rule

Department names and colors must be tenant-specific.

A department in one school must never appear in another school’s setup.

### Audit Requirements

Audit:

* Department created
* Department updated
* Department color changed
* Department activated
* Department deactivated

---

## Module 7: Programme Configuration

### Purpose

To allow tenants to define the programmes they offer.

### Required Capabilities

Tenant admin must be able to:

* Create programme
* Edit programme
* Assign programme to department
* Define programme type
* Define programme duration
* Define award name
* Define programme code
* Activate/deactivate programme

### Supported Programme Types

The system must support:

* Certificate
* Diploma
* National Diploma
* Higher National Diploma
* Technician Programme
* Professional Health Programme
* Custom Programme Type

### Required Fields

Programme should support:

* Tenant ID
* Department ID
* Programme name
* Programme code
* Programme type
* Award name
* Duration value
* Duration unit
* Status
* Description

### Business Rule

Programme type must be flexible.

The system must not force all schools into ND/HND structure only.

### Audit Requirements

Audit:

* Programme created
* Programme updated
* Programme assigned to department
* Programme activated
* Programme deactivated

---

## Module 8: Course Configuration

### Purpose

To allow schools to configure courses before admission, registration, allocation, and result processing begin.

### Required Capabilities

Tenant admin must be able to:

* Create course
* Edit course
* Assign course code
* Assign course title
* Assign course unit or credit value
* Assign course category
* Assign course to programme
* Assign course to level
* Assign course to semester
* Activate/deactivate course

### Supported Course Categories

The system must support:

* Compulsory
* Elective
* Carryover
* Practical
* Clinical/Field Posting
* Project
* Custom Course Type

### Required Fields

Course should support:

* Tenant ID
* Course code
* Course title
* Course unit
* Course category
* Department ID
* Programme ID
* Level ID
* Semester ID
* Status

### Business Rule

Course codes may repeat across tenants but must not conflict within the wrong tenant/programme scope.

### Audit Requirements

Audit:

* Course created
* Course updated
* Course mapped to programme
* Course activated
* Course deactivated

---

## Module 9: User Tenant Membership

### Purpose

To connect authenticated users to one or more tenants.

### Required Capabilities

The system must support:

* Creating user account
* Linking user to tenant
* Assigning user tenant status
* Assigning primary tenant
* Switching tenant where user belongs to more than one tenant
* Preventing access to non-member tenants

### Tenant Membership Statuses

* Active
* Pending
* Suspended
* Revoked

### Business Rule

Authentication alone does not grant tenant access.

A user must be authenticated and must have active membership in the current tenant.

### Audit Requirements

Audit:

* User added to tenant
* User removed from tenant
* Membership suspended
* Membership restored
* Tenant switch

---

## Module 10: IAM Group Foundation

### Purpose

To implement first-layer access using CodeIgniter Shield groups.

### Required IAM Groups

The following broad groups must exist:

1. Platform Admin
2. Tenant Super Admin
3. Tenant Admin
4. Lecturer
5. Student

### Group Rules

* Platform Admin belongs to the SaaS provider side.
* Tenant Super Admin belongs to one tenant and controls high-level tenant setup.
* Tenant Admin belongs to one tenant and manages assigned operational areas.
* Lecturer is an academic user.
* Student is a student portal user.

### Important Rule

Do not create Shield groups for HOD, Registrar, Bursar, Exams Officer, Course Adviser, or Admission Officer.

Those are operational authorities, not IAM groups.

### Audit Requirements

Audit:

* Group assigned
* Group removed
* User permission escalated
* User permission reduced

---

## Module 11: Operational Authority Foundation

### Purpose

To support school-specific responsibilities without corrupting Shield group structure.

### Required Operational Authorities

The system must support these authority types:

* HOD
* Registrar
* Admission Officer
* Bursar
* Accountant
* Exams Officer
* Course Adviser
* Student Affairs Officer
* ICT Officer
* Website Editor
* Result Approver
* Payment Verifier
* Clearance Officer

### Scope of Authority

Operational authority may be scoped by:

* Tenant
* Department
* Programme
* Level
* Session
* Module

### Example

A user may be:

* IAM Group: Lecturer
* Operational Authority: HOD
* Scope: Department of Community Health

Another user may be:

* IAM Group: Tenant Admin
* Operational Authority: Bursar
* Scope: Whole tenant

### Business Rule

Every operational authority must have a scope.

No authority should be unlimited unless explicitly tenant-wide.

### Audit Requirements

Audit:

* Authority assigned
* Authority removed
* Authority scope changed
* Authority used for protected action

---

## Module 12: Theme and Branding Resolver

### Purpose

To apply tenant and department identity across the system.

### Required Capabilities

The system must resolve:

* Platform default theme
* Tenant theme
* Department theme
* User-specific context theme

### Tenant Theme Fields

* Primary color
* Secondary color
* Accent color
* Logo
* Favicon

### Department Theme Fields

* Department color
* Optional department badge/icon later

### Business Rule

A student dashboard must show tenant identity and department identity.

Department color must be visible but not overwhelming.

### Audit Requirements

Audit:

* Tenant color changed
* Department color changed
* Logo changed
* Branding updated

---

## Module 13: Side Navigation Resolver

### Purpose

To ensure every user sees only allowed modules, links, and actions.

### Required Capabilities

The resolver must generate navigation based on:

* User IAM group
* Operational authority
* Active tenant
* Tenant status
* Enabled module
* Department scope
* Programme scope
* User status

### Navigation Must Support

* Parent menu
* Child menu
* Icons
* Labels
* Route names
* Sort order
* Active state
* Visibility condition
* Required IAM group
* Required operational authority

### Business Rule

Layouts must not hard-code navigation items.

Every internal layout must call the resolver.

### Security Rule

Navigation hiding is not authorization.

Routes and APIs must still enforce authorization independently.

### Audit Requirements

Audit is not required for ordinary navigation rendering.

Audit is required only when navigation/module settings are changed.

---

# 7. Required Layouts in Block 1

Block 1 must create the layout foundation for future modules.

## Required Internal Layouts

1. Platform Admin Layout
2. Tenant Admin Layout
3. Lecturer Layout
4. Student Layout

## Layout Structure

Each internal layout must have:

### Left Region

* Top: logo, tenant identity, toggle
* Middle: resolved navigation
* Bottom: user summary, settings, logout

### Right Region

* Top: page title, breadcrumb, notifications, mobile menu trigger
* Middle: main content
* Bottom: footer actions or contextual controls

## Mobile Rule

On mobile:

* Sidebar must collapse
* Navigation must be accessible through toggle
* Content must remain readable
* Tables must not break the screen
* Forms must be step-friendly

---

# 8. Database Ownership Rules for Block 1

## Platform-Owned Tables

These belong to the SaaS provider:

* Tenants
* Tenant domains
* Platform admin records
* Subscription placeholder records
* Platform module registry

## Tenant-Owned Tables

These belong to a school:

* Tenant profile settings
* Departments
* Programmes
* Levels
* Semesters
* Academic sessions
* Courses
* Course-programme mappings
* Tenant users
* Operational authorities
* Tenant theme settings
* Department color settings

## Shared Reference Tables

These may be shared:

* Nigerian states
* Nigerian LGAs
* Gender options
* Ownership type references
* Default programme type templates
* Default course category templates

## Rule

Shared reference data may be used as templates, but tenant-specific selections must be stored under tenant context.

---

# 9. Required Services

Block 1 must include service classes or equivalent service-layer logic for:

1. TenantResolutionService
2. TenantManagementService
3. TenantProfileService
4. AcademicSessionService
5. SemesterService
6. LevelService
7. DepartmentService
8. ProgrammeService
9. CourseService
10. UserTenantMembershipService
11. OperationalAuthorityService
12. NavigationResolverService
13. ThemeResolverService
14. AuditService
15. ApiResponseService

Controllers must call services. Controllers must not contain business logic.

---

# 10. Required Base Classes and Shared Components

Block 1 must establish:

## Base Controllers

* PlatformBaseController
* TenantBaseController
* LecturerBaseController
* StudentBaseController
* ApiBaseController

## Base Models

* BaseModel
* TenantScopedModel
* PlatformModel

## Required Filters

* Authentication filter
* Tenant resolution filter
* Platform admin filter
* Tenant membership filter
* IAM group filter
* Operational authority filter
* API authentication filter where applicable

## Required Helpers/Utilities

* Phone normalization helper
* Email normalization helper
* Tenant context helper
* Theme helper
* Audit helper
* API response helper

---

# 11. API and Vue Usage in Block 1

## Vue Should Be Used For

* Dynamic department list
* Programme creation forms
* Course mapping
* Level/semester dependent dropdowns
* Theme preview
* Department color preview
* Operational authority assignment
* Navigation preview where useful

## Full Page Reload Should Be Used For

* Login
* Logout
* Tenant switching
* Tenant creation by platform admin
* Final save of critical tenant setup
* User group assignment
* Operational authority final assignment

## Standard API Response Shape

All API responses must use:

```json
{
  "success": true,
  "message": "Operation completed successfully.",
  "data": {},
  "errors": {},
  "meta": {}
}
```

Sensitive actions may include:

```json
{
  "audit_reference": "AUD-2026-000001"
}
```

---

# 12. Validation Rules

## Tenant Validation

* Tenant name is required.
* Tenant slug is required.
* Tenant slug must be unique.
* Tenant email must be valid.
* Tenant phone must be normalized.
* Tenant status must be valid.

## Department Validation

* Department name is required.
* Department code is required.
* Department code must be unique within tenant.
* Department color must be valid.
* Department must belong to active tenant.

## Programme Validation

* Programme name is required.
* Programme code is required.
* Programme must belong to tenant.
* Programme must belong to a tenant department.
* Programme duration is required.
* Programme type is required.

## Course Validation

* Course code is required.
* Course title is required.
* Course must belong to tenant.
* Course must map to valid tenant programme.
* Course must map to valid tenant level.
* Course must map to valid tenant semester.

## User Membership Validation

* User must exist.
* Tenant must exist.
* Membership must not duplicate active membership.
* Tenant status must allow membership assignment.

## Operational Authority Validation

* Authority type is required.
* Authority scope is required.
* User must belong to tenant.
* Scope must belong to same tenant.

---

# 13. Required Audit Events

Block 1 must audit:

* Tenant created
* Tenant updated
* Tenant activated
* Tenant suspended
* Tenant deactivated
* Tenant profile updated
* Tenant logo changed
* Tenant theme changed
* Department created
* Department updated
* Department color changed
* Programme created
* Programme updated
* Course created
* Course updated
* Academic session created
* Academic session marked current
* Semester created
* Level created
* User added to tenant
* User removed from tenant
* IAM group assigned
* Operational authority assigned
* Operational authority removed
* Tenant switch
* Platform admin accessed tenant

---

# 14. Required Reports in Block 1

Block 1 must produce basic administrative reports.

## Platform Reports

* Tenant list
* Active tenants
* Suspended tenants
* Recently created tenants
* Tenant setup completion status

## Tenant Reports

* Department list
* Programme list
* Course list
* Academic session list
* Level list
* Semester list
* Tenant users list
* Operational authority list
* Configuration audit report

---

# 15. Block 1 Dashboards

## Platform Admin Dashboard

Must show:

* Total tenants
* Active tenants
* Suspended tenants
* Recently added tenants
* Setup status summary
* Quick action to create tenant

## Tenant Admin Dashboard

Must show:

* School profile completion
* Department count
* Programme count
* Course count
* Active academic session
* User count
* Setup checklist

## Lecturer Dashboard Placeholder

Must show:

* Tenant identity
* User identity
* “Academic assignments will appear here after course allocation is enabled.”

## Student Dashboard Placeholder

Must show:

* Tenant identity
* Student portal placeholder
* “Student services will appear here after admission and student records are enabled.”

---

# 16. Setup Completion Checklist

Each tenant must have a visible setup checklist.

A tenant is considered minimally configured when it has:

* School profile completed
* Logo uploaded
* Theme configured
* At least one academic session
* At least one semester
* At least one level
* At least one department
* At least one programme
* At least one course
* At least one tenant admin
* At least one operational authority assigned where needed

This checklist will help non-technical school users understand what remains before they can use admission, fees, students, and results modules.

---

# 17. Access Control Matrix

## Platform Admin

Can:

* Create tenants
* View tenants
* Suspend tenants
* Access platform dashboard
* View platform audit
* Access tenant overview in controlled mode

Cannot:

* Perform normal tenant operations casually without audit
* Modify student/result/payment records without explicit platform support workflow in later blocks

## Tenant Super Admin

Can:

* Configure school profile
* Configure departments
* Configure programmes
* Configure courses
* Configure sessions
* Configure levels
* Manage tenant users
* Assign tenant admins
* Assign operational authorities
* View tenant audit reports

Cannot:

* Access another tenant
* Modify platform-level tenant subscription records

## Tenant Admin

Can:

* Manage assigned configuration areas
* View tenant dashboard
* Access modules allowed by operational authority

Cannot:

* Access platform admin area
* Access another tenant
* Assign tenant super admin unless permitted

## Lecturer

Can:

* Access lecturer layout
* View assigned academic placeholders

Cannot in Block 1:

* Manage courses as lecturer
* Submit results
* Register students
* Approve records

## Student

Can:

* Access student layout placeholder

Cannot in Block 1:

* Apply for admission inside student dashboard
* Register courses
* View results
* Make payments

---

# 18. Implementation Order for Block 1

Developers must implement Block 1 in this order:

## Step 1: CI4 and Shield Base

* Install and configure CodeIgniter 4
* Install and configure Shield
* Enable email login
* Add phone-number login support
* Normalize email and phone identifiers
* Create primary IAM groups

## Step 2: Tenant Core

* Create tenant records
* Create tenant domain records
* Build tenant resolver
* Build tenant context service
* Create tenant-scoped base model
* Enforce tenant context in tenant-owned queries

## Step 3: Platform Admin Area

* Platform admin layout
* Tenant list
* Tenant creation
* Tenant status management
* Platform dashboard

## Step 4: Tenant Membership

* User-to-tenant membership
* Active tenant resolution
* Tenant switching
* Tenant membership enforcement

## Step 5: Tenant Admin Area

* Tenant admin layout
* Tenant dashboard
* Tenant setup checklist
* Tenant profile configuration

## Step 6: Academic Configuration

* Academic sessions
* Semesters
* Levels
* Departments
* Programmes
* Courses
* Course mapping

## Step 7: Role and Authority Foundation

* Shield group assignment
* Operational authority types
* Operational authority assignment
* Scoped authority enforcement

## Step 8: Theme and Navigation

* Tenant theme resolver
* Department color resolver
* Side navigation resolver
* Mobile sidebar behavior

## Step 9: Audit and Reports

* Configuration audit logs
* Tenant setup reports
* Platform tenant reports
* Tenant configuration reports

## Step 10: Final Stabilization

* Tenant isolation testing
* Permission testing
* Mobile testing
* Audit testing
* Setup completion testing

---

# 19. Testing Checklist

## Tenant Isolation Tests

* Tenant A cannot see Tenant B departments.
* Tenant A cannot see Tenant B programmes.
* Tenant A cannot see Tenant B courses.
* Tenant A cannot see Tenant B users.
* Tenant A cannot access Tenant B files.
* Tenant A cannot access Tenant B configuration URLs by changing IDs.

## Authentication Tests

* User can login with email.
* User can login with Nigerian phone number.
* Invalid phone format is rejected.
* Suspended user cannot login.
* User without tenant membership cannot access tenant dashboard.

## Authorization Tests

* Platform admin can access platform dashboard.
* Tenant admin cannot access platform dashboard.
* Lecturer cannot access tenant setup pages.
* Student cannot access admin pages.
* Operational authority limits access correctly.

## Configuration Tests

* Tenant can create department.
* Tenant can create programme.
* Tenant can create level.
* Tenant can create semester.
* Tenant can create session.
* Tenant can create course.
* Tenant can assign department color.
* Tenant can configure theme.

## Audit Tests

* Tenant creation is audited.
* Department creation is audited.
* Programme update is audited.
* Course update is audited.
* Role assignment is audited.
* Operational authority assignment is audited.

## Mobile Tests

* Sidebar collapses correctly.
* Forms are usable on mobile.
* Dashboard cards are readable.
* Tables do not overflow badly.
* Main actions are touch-friendly.

---

# 20. Acceptance Criteria

Block 1 is complete only when:

1. Platform admin can create and manage tenants.

2. A tenant can configure its school profile.

3. A tenant can configure sessions, semesters, levels, departments, programmes, and courses.

4. A tenant can configure school theme and department colors.

5. A tenant can create/manage tenant users.

6. CodeIgniter Shield handles authentication.

7. Login supports email and Nigerian phone number.

8. IAM groups are working.

9. Operational authority assignment is working.

10. Side navigation changes according to user group and authority.

11. Tenant isolation is enforced at model, service, route, and UI level.

12. All configuration actions are audited.

13. Platform admin, tenant admin, lecturer, and student layouts exist.

14. Mobile layout is usable.

15. No tenant-owned display data is hard-coded.

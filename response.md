E. Features Already Implemented
1. Tenant Foundation
Implemented pieces include:

Tenant registry tables:

tenants

tenant_domains

tenant_memberships 

Tenant context resolution:

app/Services/Tenancy/TenantResolver.php

app/Services/Tenancy/TenantContextManager.php

app/Entities/TenantContext.php

Tenant filters:

app/Filters/TenantContextFilter.php

app/Filters/TenantAccessFilter.php

app/Filters/PublicTenantFilter.php

app/Filters/ApplicantAccessFilter.php

Tenant-scoped base model:

app/Models/TenantScopedModel.php

The tenant-scoped model is an important safety layer because it scopes model builders by tenant_id and fails closed when tenant context is unresolved. 

2. Tenant Configuration Foundation
Implemented areas include tenant profile, academic sessions, semesters, levels, departments, programmes, courses, and programme-course mappings through Tenant\ConfigurationController and tenant models. Routes are present under /tenant/config. 

Relevant models include:

app/Models/Tenant/TenantProfileModel.php

app/Models/Tenant/AcademicSessionModel.php

app/Models/Tenant/SemesterModel.php

app/Models/Tenant/LevelModel.php

app/Models/Tenant/DepartmentModel.php

app/Models/Tenant/ProgrammeModel.php

app/Models/Tenant/CourseModel.php

app/Models/Tenant/ProgrammeCourseModel.php

3. Tenant Access, Authorities, Navigation
Implemented areas include:

Tenant memberships.

IAM group assignments.

Operational authorities.

Authority grants.

Navigation resolver.

Routes exist under /tenant/access, guarded by tenant.access.manage. 

The tenant access filter enforces active membership first, then optional group and authority requirements. 

4. Public Website / Website CMS
Implemented areas include:

Public homepage/about/contact.

Public departments and programmes.

Public admissions page.

Public admission lists and admission programmes.

Public apply entry.

Public news, announcements, calendar.

Public management and gallery.

Tenant media management.

Tenant showcase CMS.

Editorial lifecycle.

Institutional showcase CMS.

Website dashboard.

Website settings.

Website menu.

Website audit.

Public routes are tenant-context and public-tenant guarded.  Equivalent slug routes exist under /t/{tenantSlug}. 

Tenant website CMS routes are guarded by specific operational authorities such as website.media.manage, website.content.edit, website.content.publish, website.dashboard.view, website.settings.manage, website.menu.manage, and website.audit.view. 

5. Admissions Block 3
Implemented areas include:

Public admission discovery.

Applicant profile.

Applicant dashboard.

Application draft.

Biodata autosave.

O’Level capture.

Document upload/download.

Preview and submission.

Application review.

Document review.

Screening.

Shortlist batches.

Decisions.

Offers.

Applicant offer acceptance/decline.

Admission list publication.

Acceptance/clearance placeholder.

Conversion eligibility marker.

Reports and outbox operations.

Applicant routes are protected by Shield/auth, tenant context, CSRF on mutations, and applicant ownership filtering. 

Admissions staff routes are split by authority: review, document review, screening, decisions, shortlisting, approval, list preview/publication, acceptance/clearance, reports, and audit/operations. 

F. Features Partially Implemented
1. Platform Tenant Management
The documentation expects platform admin login, tenant creation, activation/suspension/deactivation, domain/subdomain management, subscription placeholder, and controlled tenant overview. The code has /platform/tenants GET/POST skeleton routes. 

Risk: The route group does not show explicit protectedAuth or platform-admin authority filters in Routes.php. This should be reviewed before treating platform tenant management as secure.

2. Block 1 UI Completeness
Tenant configuration endpoints exist, but the implementation appears API/controller-oriented rather than a full polished UI for all Block 1 configuration screens. Tenant admin layouts exist, but configuration management views were not visible in the same completeness level as website/admission views.

3. Shield / Login Integration
Shield routes are registered, and applicant identifier normalization exists, but the full login/registration UX and phone/email login behavior need runtime verification. 

4. Acceptance/Clearance Handoff
Acceptance and clearance are implemented as placeholders/markers, not full student record conversion. This matches the documented boundary: Block 3 prepares the applicant for Block 5, but does not create student records. The migration explicitly calls this a Block 5 handoff marker. 

5. Reports and Operations
Admissions reporting and outbox retry surfaces exist, but should be considered an operational contract until verified with seeded data and CSV export testing. Routes exist under /tenant/admissions/reports and /tenant/admissions/operations. 

G. Features Not Yet Implemented
1. Block 4 — Fees and Levy Payment
No main code module found for:

Fee types.

Fee schedules.

Fee applicability.

Invoices.

Payment records.

Payment gateways.

Receipts.

Waivers.

Debt tracking.

Payment eligibility service.

Admission application fee.

Acceptance fee enforcement.

The Block 4 documentation expects a tenant-configurable financial rules engine. 

2. Block 5 — Student and Staff Records
No dedicated module found for:

Student records.

Staff records.

Staff employment profiles.

Student programme/level placement records.

Student lifecycle.

Staff lifecycle.

Conversion from accepted applicant to student record.

The Block 5 roadmap defines these as the trusted source of truth for students and staff. 

3. Block 6 — Academic Operations
No module found for:

Staff course allocation.

Lecturer assignment.

Course adviser assignment.

Student course registration.

Add/drop.

Course registration approvals.

Class lists.

Block 6 documentation explicitly depends on stable Block 1 and Block 5, plus payment eligibility stubs or implementation. 

4. Block 7 — Results
No module found for:

Grading rules.

Score entry.

Result computation.

Result approvals.

Publication.

Corrections.

Student result viewing.

Block 7 depends strictly on Blocks 1–6. 

H. Possible Bugs, Risks, and Inconsistencies
1. Dependencies/vendor appear missing or incomplete
composer test failed because phpunit was not found. php spark routes and php spark migrate:status failed because vendor/codeigniter4/framework/system/Boot.php was missing.

This means runtime route verification, migrations, and automated tests cannot currently run until dependencies are installed/restored.

2. Untracked node_modules/
git status --short showed ?? node_modules/. I did not create or modify it during this audit. It should be reviewed against .gitignore; typically node_modules/ should not be committed.

3. Platform tenant routes may lack protection
The platform tenant routes are grouped without a visible protectedAuth or platform-admin authority filter. 

This is high risk if those endpoints perform real mutations. Even if currently skeleton-only, secure them before expanding platform onboarding.

4. Some tenant configuration routes lack fine-grained authority filters
/tenant/config is protected by protectedAuth,tenantContext:required, but the route group itself does not visibly require a specific operational authority like school.configuration.manage. 

This may be enforced inside services/controllers, but the route layer is weaker than the website/admission route patterns.

5. Public and tenant website routes are strong, but need runtime verification
Routes are well-separated by tenant context, public tenant guard, CSRF, and granular authority, but runtime tests could not be executed because dependencies are missing.

6. Tenant isolation relies heavily on TenantScopedModel
This is good, but any direct $db->table() queries in services/controllers must manually include tenant_id filters. The codebase contains tests that check some of those flows, but a full audit of every direct query should be performed before new blocks are added.

7. Later block dependencies are not ready
Do not start results or academic operations. Blocks 6 and 7 explicitly depend on student/staff records and payment eligibility. 

8. Admission acceptance fee is represented only as status text
The acceptance/clearance tables include acceptance_fee_status, but there is no real payment module behind it yet. 

Until Block 4 exists, any acceptance-fee status must be treated as a placeholder, not financial truth.

9. Documentation says Vue Composition API may be used, but current implementation is mostly server-rendered
This is not necessarily a bug. The docs say Vue may be used “where appropriate,” not that it is mandatory. However, future interactive screens should follow the existing server-rendered + service-backed approach unless a real need for Vue islands exists.

I. Testing Guide for Existing Work
Because dependencies are currently unavailable, this is the guide for what should be tested once composer install restores vendor dependencies.

1. Tenant Foundation Tests
Required data
At least two tenants:

Tenant A: active.

Tenant B: active.

At least one tenant domain for each.

At least one user with active membership in Tenant A.

Optional second user with active membership in Tenant B.

Manual browser tests
Visit /t/{tenantA} and confirm Tenant A public website loads only Tenant A content.

Visit /t/{tenantB} and confirm Tenant B public website loads only Tenant B content.

Visit /internal/tenant-context as an authenticated user with Tenant A membership.

Visit tenant pages without tenant context and confirm safe denial/unavailable response.

API/HTTP tests
GET /internal/tenant-context

Expected success: returns resolved tenant for valid authenticated membership.

Expected failure: forbidden/unresolved when no tenant can be resolved.

GET /tenant/navigation

Expected success: active member receives navigation.

Expected failure: non-member receives 403.

Database verification
Verify tenants, tenant_domains, and tenant_memberships rows.

Verify tenant-scoped tables include tenant_id.

Authentication/permission tests
User with no membership cannot access tenant-protected route.

User with membership but no required authority cannot access authority-protected route.

User with correct authority can access.

Tenant isolation tests
Create same type of record under Tenant A and Tenant B.

Query from Tenant A context.

Confirm Tenant B record is invisible.

2. Tenant Configuration Tests
Required data
Active tenant.

Authenticated tenant admin or user with configuration authority.

Academic session, semester, level, department, programme, course data.

Routes/forms to test
POST /tenant/config/profile

POST /tenant/config/academic-sessions

POST /tenant/config/semesters

POST /tenant/config/levels

POST /tenant/config/departments

POST /tenant/config/programmes

POST /tenant/config/courses

POST /tenant/config/programme-courses

Routes are registered under /tenant/config. 

Expected successful behavior
Records are created with current tenant_id.

created_by and updated_by are populated when authenticated.

Invalid references are rejected.

Duplicate codes/names should be rejected where unique constraints exist.

Expected failure/security behavior
Missing tenant context fails.

Non-authenticated request fails.

Cross-tenant department/programme/course references fail.

Edge cases
Duplicate academic session code/name.

Programme with department from another tenant.

Programme-course map using course from another tenant.

Invalid department color hex.

3. Website Public Site Tests
Required data
Active tenant.

website_settings with public enabled.

Tenant profile fallback data.

Public menu items.

Published departments/programmes/content/gallery/management records.

Manual browser tests
Visit:

/t/{tenantSlug}

/t/{tenantSlug}/about

/t/{tenantSlug}/contact

/t/{tenantSlug}/departments

/t/{tenantSlug}/departments/{slug}

/t/{tenantSlug}/programmes

/t/{tenantSlug}/programmes/{slug}

/t/{tenantSlug}/admissions

/t/{tenantSlug}/news

/t/{tenantSlug}/announcements

/t/{tenantSlug}/calendar

/t/{tenantSlug}/management

/t/{tenantSlug}/gallery

These routes exist in both hostname-root and slug-prefixed forms. 

Expected successful behavior
Public pages load tenant-specific content.

Draft/private/future content does not display.

Branding uses tenant settings/theme.

Canonical/public links resolve consistently.

Disabled public website shows neutral unavailable page.

Expected failure/security behavior
Suspended or inactive tenant should not expose public pages.

Tenant B public content must not appear on Tenant A hostname/slug.

Private media cannot be fetched through public media route.

4. Website CMS Tests
Required roles/authorities
website.dashboard.view

website.settings.manage

website.menu.manage

website.media.manage

website.content.view

website.content.create

website.content.edit

website.content.publish

website.content.archive

website.content.delete

website.audit.view

Routes/forms to test
GET /tenant/website

GET|POST /tenant/website/settings

GET|POST /tenant/website/menu

POST /tenant/website/menu/reorder

POST /tenant/website/menu/{id}/archive

GET /tenant/website/media

POST /tenant/website/media/upload

PATCH /tenant/website/media/{id}/visibility

DELETE /tenant/website/media/{id}

GET /tenant/website/media/{id}/private

GET /tenant/website/editorial/{section}

POST /tenant/website/editorial/{section}/draft

POST /tenant/website/editorial/{section}/{id}/publish

POST /tenant/website/editorial/{section}/{id}/archive

GET /tenant/website/audit

Website CMS route groups are authority-gated in Routes.php. 

Expected behavior
Users only access routes for granted authorities.

CSRF is required for mutations.

Media validation rejects invalid MIME/extension/oversize images.

Menu parent references cannot cross tenants.

Editorial publish requires publish authority.

Audit entries are tenant-scoped.

Edge cases
Upload SVG/executable renamed as image.

Upload overly large image.

Archive a menu item with children.

Schedule content in the future.

Publish without required title/body.

Reorder gallery/menu with foreign tenant IDs.

5. Admission Configuration Tests
Required roles/authorities
admissions.dashboard.view

admissions.cycles.manage

admissions.programmes.manage

admissions.requirements.manage

Routes/forms to test
GET /tenant/admissions

GET|POST /tenant/admissions/cycles

POST /tenant/admissions/cycles/{id}/transition/{state}

GET|POST /tenant/admissions/programmes

GET /tenant/admissions/requirements

POST /tenant/admissions/requirements/definitions

POST /tenant/admissions/requirements/subjects

POST /tenant/admissions/requirements/documents

Admissions configuration route groups are declared at the end of Routes.php. 

Expected behavior
Cycle opens/closes/archives only through valid transitions.

Programme opening references only tenant-owned academic session/programme/level/department.

Public discovery returns only open/public cycles and active programme openings.

Requirement definitions remain scoped to cycle/opening.

Edge cases
Archived cycle reopened.

Opening created with Tenant B programme in Tenant A context.

Document requirement with invalid MIME list.

Subject requirement with non-reference subject code.

6. Applicant Portal Tests
Required data
Active tenant.

Public website enabled.

Open admission cycle.

Open programme.

Applicant Shield account.

Applicant profile.

Manual browser tests
GET /t/{tenantSlug}/apply

Login/register through Shield.

GET /applicant/profile

POST /applicant/profile

GET /applicant

POST /applicant/applications

GET /applicant/applications/{token}

Save biodata.

Save O’Level.

Upload documents.

Preview application.

Submit application.

Applicant routes are registered and protected by auth, tenant context, applicant access, and CSRF for mutations. 

Expected successful behavior
Applicant can create one profile per tenant/user.

Applicant can start draft for open programme.

Draft token is private/unguessable.

Biodata saves and completion percent updates.

O’Level rows save.

Documents store privately.

Submission generates application number/snapshot.

Re-submission is idempotent.

Expected failure/security behavior
Applicant cannot access another applicant’s draft.

Applicant cannot access another tenant’s draft.

Submitted application should not be edited unless correction window permits it.

Private document download should require ownership or reviewer authorization.

Edge cases
Multiple O’Level sittings.

Missing required document.

Invalid Nigerian phone.

Oversized document.

Unsupported MIME.

Duplicate submit clicks.

7. Admission Review / Decisions / Offers Tests
Required roles/authorities
admissions.applications.view

admissions.applications.review

admissions.documents.review

admissions.screening.manage

admissions.shortlist.manage

admissions.decisions.manage

admissions.decisions.approve

Routes/forms to test
GET /tenant/admissions/applications

GET /tenant/admissions/applications/{id}

POST /tenant/admissions/applications/{id}/review

POST /tenant/admissions/documents/{id}/review

POST /tenant/admissions/applications/{id}/screening

GET /tenant/admissions/decisions

POST /tenant/admissions/decisions/batches

POST /tenant/admissions/decisions/batches/{batchId}/applications/{applicationId}

POST /tenant/admissions/decisions/applications/{applicationId}

POST /tenant/admissions/decisions/{decisionId}/approve

Review routes are authority-gated.  Decision routes are separately gated. 

Expected behavior
Staff reviewer sees only tenant applications.

Review action updates review status and audit.

Document review logs immutable review decision.

Screening record created under tenant.

Decision creates current decision and possibly offer.

Approvals require approval authority.

Prior current decisions are superseded safely.

Edge cases
Decide on non-submitted application.

Approve already-approved decision.

Offer when active offer already exists.

Review Tenant B application in Tenant A context.

8. Admission List Publication Tests
Required roles/authorities
admissions.lists.preview

admissions.lists.publish

Routes/forms to test
GET /tenant/admissions/lists

POST /tenant/admissions/lists

GET /tenant/admissions/lists/{id}

POST /tenant/admissions/lists/{id}/entries

POST /tenant/admissions/lists/{id}/publish

GET /t/{tenantSlug}/admissions/lists

GET /t/{tenantSlug}/admissions/lists/{token}

List routes exist for staff and public views. 

Expected behavior
Draft list is visible only to authorized staff.

Public sees only published list.

Published list exposes safe fields only.

Private notes, phone, documents, and internal IDs are not exposed.

Version numbers are unique per tenant/cycle.

Edge cases
Publish empty list.

Add application from another tenant.

Publish same list twice.

Revoke/archive behavior if supported.

9. Acceptance / Clearance / Handoff Tests
Required roles/authorities
Applicant account with offer.

Staff user with:

admissions.acceptance.view

admissions.clearance.manage

Routes/forms to test
GET /applicant/offers

POST /applicant/offers/accept

POST /applicant/offers/decline

GET /tenant/admissions/acceptance

POST /tenant/admissions/acceptance/applications/{id}/clearance

POST /tenant/admissions/acceptance/applications/{id}/eligibility

Acceptance routes are registered for applicants and staff. 

Expected behavior
Applicant sees only own offer.

Applicant can accept/decline once according to business rules.

Staff can update clearance status.

Eligibility marker stores Block 5 handoff payload.

No student record is created yet.

Edge cases
Accept expired offer.

Accept revoked offer.

Decline after accept.

Staff clears applicant from another tenant.

Eligibility marker created twice.

10. Admission Reports / Operations Tests
Required roles/authorities
admissions.reports.view

admissions.audit.view

Routes/forms to test
GET /tenant/admissions/reports

GET /tenant/admissions/reports/export/{type}

GET /tenant/admissions/operations

POST /tenant/admissions/operations/outbox/{id}/retry

Routes exist under admissions reports and operations. 

Expected behavior
Reports include only current tenant.

CSV excludes private fields.

Outbox retry only works for retryable tenant-owned row.

Audit log is tenant-scoped.

Edge cases
Export unknown report type.

Retry already-sent notification.

Retry Tenant B outbox in Tenant A context.

J. Database and Migration Observations
Existing migration coverage
The codebase includes migrations for:

Tenant foundation.

Tenant configuration.

Tenant access control.

Theme/audit.

Website media.

Public website settings/menu.

Academic showcase.

Editorial content.

Institutional showcase.

Admission readiness.

Admission configuration.

Draft applications.

Submission/O’Level/outbox.

Review workspace.

Decisions/offers.

List publication.

Acceptance/clearance/handoff.

Good patterns
Tenant-owned tables usually include tenant_id.

Many tables have unique tenant composite keys.

Many admission and website tables use soft-delete-capable fields.

TenantScopedModel provides model-layer tenant scoping and actor attribution. 

Issues to verify
Ensure every tenant-owned table has an associated tenant-scoped model.

Ensure any direct query builder usage includes tenant_id.

Ensure foreign keys that reference tenant-owned records are checked for same-tenant relationships in services, not only database constraints.

Ensure no later migration introduces tables without tenant_id unless genuinely platform-owned or shared-reference.

K. Authentication, Authorization, and Role Observations
Good patterns
Shield routes are registered under /auth. 

Tenant routes generally use protectedAuth.

Tenant context is required for tenant-sensitive private routes.

Website/admission routes use granular operational authorities.

Tenant access filter checks membership before groups/authorities. 

Risks
Platform tenant routes do not visibly include platform-admin auth/authority filters. 

Tenant config route group lacks visible authority requirements beyond auth/context. 

Need verify Shield session integration. TenantResolver uses session membership fallback, and the exact relationship with Shield identities should be tested thoroughly.

Need confirm applicant and staff users cannot overlap into unintended privileges without proper tenant membership/authority records.

L. Tenant Isolation / Multi-school Safety Observations
Strong safety mechanisms
Tenant context is centralized.

Public tenant guard separates tenant resolution from public eligibility.

Tenant-scoped model fails closed without tenant context.

Tenant-owned inserts overwrite browser-provided tenant_id, created_by, and updated_by.

Routes generally separate public, applicant, tenant staff, and platform surfaces.

Areas needing caution
Direct database queries are still a possible leak vector.

Cross-tenant foreign-key relationships need service-level validation because database foreign keys alone do not guarantee same-tenant relationship.

File storage must always resolve through tenant-owned metadata; public URLs must never expose raw storage paths.

Admission public lists must never expose private application fields.

Reports and CSV exports must be reviewed carefully for private notes, phone numbers, document paths, and internal review data.

M. Recommended Safe Continuation Plan
Step 1 — Restore runnable environment first
Before coding:

Run composer install.

Confirm vendor/codeigniter4/framework/system/Boot.php exists.

Confirm PHPUnit binary exists.

Run full tests.

Run php spark routes.

Run php spark migrate:status.

Do not add new modules while the application cannot boot.

Step 2 — Resolve repository hygiene
Review untracked node_modules/.

Ensure node_modules/ is ignored and not committed.

Confirm no generated vendor/runtime artifacts are tracked accidentally.

Step 3 — Harden Block 1 security boundaries
Before extending anything:

Add/verify protection for /platform/tenants.

Add/verify platform-admin authority model.

Add/verify route-level or service-level authority for /tenant/config.

Add feature tests for unauthorized tenant configuration attempts.

Verify all configuration services reject cross-tenant references.

Step 4 — Complete Block 1 operational UI/API gaps
Only after security hardening:

Confirm tenant onboarding flow.

Confirm tenant profile CRUD.

Confirm academic configuration CRUD.

Confirm access management flow.

Confirm navigation and layouts.

Confirm audit logs for all configuration mutations.

Step 5 — Stabilize Block 2 and Block 3 with full regression tests
Before Blocks 4–7:

Run website phase tests.

Run admission phase tests.

Add browser-level smoke tests for:

Public site.

CMS.

Applicant application submission.

Admission staff review/decision/list publication.

Test two-tenant isolation end to end.

Step 6 — Implement Block 4 next, not Block 5/6/7
The safest next new development block is Block 4: Fees and Levy Payment, because:

Block 5 depends on payment foundations for student fee status.

Block 6 depends on payment eligibility checks.

Block 7 depends on payment control for result viewing.

Current admissions acceptance fee is only a placeholder.

Block 4 should start with a narrow foundation:

Fee category/type tables.

Fee schedule tables.

Invoice table.

Payment transaction/manual verification table.

Payment eligibility service contract.

Stub gateway interface.

Admission application fee and acceptance fee hooks.

Tenant-scoped audit.

Step 7 — Do not touch Blocks 6 or 7 yet
Course allocation, registration, and results should wait until:

Block 4 payment eligibility exists.

Block 5 student/staff records exist.

Accepted applicants can be converted to students.

Staff identities/lecturer records exist.

N. Files to Touch Next
When development resumes, safest candidates are:

Environment / project hygiene
.gitignore

composer.json

phpunit.dist.xml

Security hardening
app/Config/Routes.php

app/Controllers/Platform/TenantController.php

app/Controllers/Tenant/ConfigurationController.php

app/Services/TenantAccessService.php

app/Filters/TenantAccessFilter.php

Block 1 completion
app/Services/Tenancy/TenantResolver.php

app/Services/NavigationResolver.php

app/Services/AuditLogger.php

app/Models/Tenant/*

app/Database/Migrations/2026-05-28-*

Block 4 start point
New files should likely be introduced under:

app/Database/Migrations/

app/Models/Tenant/Fees/

app/Services/Fees/

app/Controllers/Tenant/Fees/

app/Views/tenant/fees/

tests/unit/

tests/database/

O. Files to Avoid Touching for Now
Avoid broad changes to these until environment and regression tests are working:

Admission decision/list/acceptance services:

app/Services/Admissions/AdmissionDecisionService.php

app/Services/Admissions/AdmissionListPublicationService.php

app/Services/Admissions/AdmissionAcceptanceService.php

Applicant ownership/security services:

app/Services/Admissions/ApplicantAccessPolicy.php

app/Services/Admissions/ApplicantDocumentStorage.php

app/Services/Admissions/ApplicantDocumentService.php

Tenant isolation base:

app/Models/TenantScopedModel.php

Existing admissions migrations:

app/Database/Migrations/2026-06-*

Existing website CMS migrations/services:

app/Database/Migrations/2026-05-31-*

app/Services/Website/*

Reason: these areas are already interconnected and security-sensitive. Modify them only with focused tests.

P. Final Recommendation
The project is not empty or merely planned. It has a substantial implementation of the tenant foundation, public website CMS, and admission system. However, it is not ready for later academic modules because Fees, Student/Staff Records, Course Operations, and Results are not implemented.

The safest next move is:

Restore dependencies and make the application boot.

Run the existing test suite.

Harden platform and tenant configuration authorization.

Add missing regression tests around Block 1 security.

Then begin Block 4 fees/payment foundation with a clean tenant-scoped design.

Do not begin Block 5, Block 6, or Block 7 until Block 4 and the Block 1 hardening are stable.
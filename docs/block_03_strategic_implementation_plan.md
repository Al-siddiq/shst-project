# Block 3 Strategic Implementation Plan
## Student Application and Admission System

---

## Document Status

**Project:** SaaS Multi-Tenant Information Management System for Nigerian Schools of Health Sciences and Technology
**Block:** Block 3
**Block Name:** Student Application and Admission System
**Purpose:** Execution-ready implementation strategy for the current CodeIgniter 4 repository
**Backend:** CodeIgniter 4
**Identity Foundation:** CodeIgniter Shield
**Frontend Enhancement:** Vue.js Composition API where interaction benefits from it
**Depends On:** Block 1 — SaaS and School Configuration; Block 2 — Public Website and Institutional Showcase
**Delivers:** A tenant-resolved, mobile-first applicant-to-admission workflow that ends with an auditable student-conversion eligibility marker

---

# 1. Executive Summary

Block 3 turns the configured tenant school and its public website into a usable admissions operation. A prospective applicant must be able to enter from the resolved tenant website, create or access an applicant account with an email address or Nigerian phone number, complete a resumable application, submit it, receive an admission decision, accept an offer, and track clearance status. Authorized tenant staff must be able to configure admissions, review submitted applications, record screening outcomes, shortlist applicants, issue offers, publish admission lists, and report on the pipeline.

Every admissions record is tenant-owned. Every route that reads or mutates admissions data must resolve tenant context before using a tenant-scoped model. Every sensitive mutation must also enforce authentication, tenant membership where staff access is involved, operational authority, server-side validation, lifecycle rules, and audit logging. Applicant ownership checks are mandatory in addition to tenant scope: resolving the correct tenant does not authorize one applicant to access another applicant's records.

Block 3 has four surfaces:

1. **Public admissions discovery:** database-driven admission-cycle and open-programme information on the tenant public website.
2. **Applicant portal:** mobile-first registration, login, draft completion, submission, offer, acceptance, and clearance tracking.
3. **Tenant admissions workspace:** configuration, review, screening, shortlisting, decision, publication, reporting, and audit views for authorized staff.
4. **Downstream handoff contract:** an auditable marker that an accepted and cleared applicant is eligible for conversion into a student by Block 5.

Block 3 deliberately does **not** implement a payment engine, student profile management, course registration, result processing, or staff records. Application-fee and acceptance-fee fields are configuration and integration placeholders only. Payment gateway initiation, receipts, reconciliation, and ledger logic belong to Block 4. Student record creation belongs to Block 5.

---

# 2. Source Documents Reviewed

This strategy is based on the complete documentation set in `docs/`:

| Document | Block 3 relevance |
| --- | --- |
| `Foundation-Concept-Documents.md` | Defines shared-schema tenancy, tenant resolution, CI4 layering, Shield-based identity, two-layer authorization, mobile-first UI, Vue request flow, audit logging, file visibility, lifecycle discipline, numbering, validation, approval, and security rules. |
| `shst_saas_blueprint.md` | Establishes the configurable SaaS product philosophy and the standard applicant-to-student journey. |
| `block_01_SaaS_and_school_configuration.md` | Defines the tenant, academic-session, department, programme, level, membership, authority, theme, navigation, and audit foundations Block 3 must reuse. |
| `block_02_public_website_roadmap.md` | Defines the tenant-resolved public website, admission-information boundary, public media foundation, and application-link placeholder that Block 3 must extend. |
| `block_02_strategic_implementation_plan.md` | Establishes the repository-specific execution-plan format and CI4 implementation patterns already used in the project. |
| `block_02_phase_0_readiness.md` through `block_02_phase_5_operations_and_stabilization.md` | Record the staged Block 2 delivery baseline and confirm the current public website capabilities available to admissions. |
| `block_03_admission_roadmap.md` | Defines the authoritative Block 3 scope, fourteen modules, workflow states, reports, access model, mobile expectations, and acceptance criteria. |
| `block_04_fees_payment_roadmap.md` | Defines the payment boundary: fees, invoices, gateway transactions, receipts, reconciliation, and financial reports remain in Block 4. |
| `block_05_records_roadmap.md` | Defines the downstream boundary: student and staff record management remain in Block 5. |
| `block_06_academic_operations_roadmap.md` | Confirms course allocation and course registration remain in Block 6. |
| `block_07_results_roadmap.md` | Confirms result configuration, entry, computation, approval, publication, and correction remain in Block 7. |

---

# 3. Repository Baseline and Entry Gate

## 3.1 Existing Foundations to Reuse

The current repository already provides useful Block 1 and Block 2 foundations:

| Existing foundation | Current implementation | Block 3 use |
| --- | --- | --- |
| Tenant request context | `TenantResolver`, `TenantContextManager`, `TenantContextFilter`, and `PublicTenantFilter` | Resolve tenant identity for public admission discovery, applicant portal requests, and tenant staff workspaces. |
| Tenant-scoped model base | `TenantScopedModel` | Constrain tenant-owned admissions queries and inject trusted ownership and actor metadata. |
| Tenant access control | `TenantAccessService`, `TenantAccessFilter`, tenant membership records, IAM assignments, and operational authorities | Protect admissions configuration, review, decision, publication, reporting, and clearance actions. |
| Audit service | `AuditLogger` and `AuditLogModel` | Persist sensitive admissions events with actor, tenant, target, summary, and metadata. |
| API convention | `ApiResponseTrait` | Keep Vue-facing JSON responses predictable. |
| Academic reference records | Academic sessions, departments, programmes, and levels | Configure admission cycles and programme openings without duplicating tenant academic records. |
| Public website admission page | Tenant-resolved admissions page and CMS showcase records | Display current admission information and link visitors into the applicant start flow. |
| File/media patterns | Private writable storage, metadata records, generated paths, and controlled download delivery | Inform applicant-document storage without reusing public website media as applicant documents. |
| Vue build path | Vite source assets and compiled public assets | Add focused applicant-form and admin-workspace enhancements where server-rendered pages alone are insufficient. |

## 3.2 Required Entry-Gate Hardening Before Feature Work

Block 3 must begin by closing the following gaps. These are prerequisites, not optional refinements.

### A. Install and configure CodeIgniter Shield

`composer.json` currently installs the CI4 framework but does not install Shield. The existing `IdentityGuard` can use Shield when `auth()` exists and falls back to a session user ID, which is useful for incremental development but is not the final Block 3 security posture.

Required behavior:

- install and configure CodeIgniter Shield as the identity foundation;
- run and verify the Shield migrations;
- preserve global user identity while keeping tenant-specific applicant profiles and applications tenant-owned;
- configure secure login, logout, throttling, session regeneration, and verification flows;
- normalize and support both email address and Nigerian phone-number identifiers;
- do not rely on a browser-supplied user ID, applicant ID, or tenant ID;
- retain the adapter boundary so controllers and services do not depend directly on session internals.

### B. Define applicant identity and tenant-entry rules

An applicant is an authenticated portal user, but does not need a tenant staff membership or a staff operational authority. Applicant access is authorized by all of the following:

1. Shield authentication;
2. resolved active tenant context;
3. tenant-owned applicant profile associated with the authenticated global user;
4. ownership of the requested application, document, offer, or acceptance record;
5. lifecycle permission for the requested action.

The canonical start flow must originate from a tenant-resolved public website route such as `/apply`. If an installation also supports a central entry page, it must require explicit school selection before an applicant profile or application is created. Tenant identity must never be inferred from an applicant-controlled hidden form field alone.

### C. Add admissions-specific authorities

Provision tenant operational authorities separately from Shield groups. At minimum add:

- `admissions.dashboard.view`
- `admissions.cycles.manage`
- `admissions.programmes.manage`
- `admissions.requirements.manage`
- `admissions.applications.view`
- `admissions.applications.review`
- `admissions.documents.review`
- `admissions.screening.manage`
- `admissions.shortlist.manage`
- `admissions.decisions.manage`
- `admissions.decisions.approve`
- `admissions.lists.preview`
- `admissions.lists.publish`
- `admissions.acceptance.view`
- `admissions.clearance.manage`
- `admissions.reports.view`
- `admissions.audit.view`

Where appropriate, authority assignments may be narrowed by department or programme scope. UI visibility may reflect permissions, but route filters and service policies remain authoritative.

### D. Separate applicant documents from public website media

Applicant passports, credentials, and supporting documents are private admissions files. They must not be stored as public website media and must not be exposed through public derivative endpoints.

Required behavior:

- store documents beneath a server-generated tenant admissions path in writable storage;
- record tenant ID, applicant/application ownership, document type, MIME type, size, checksum, storage key, review status, and timestamps;
- reject arbitrary client filesystem paths, traversal, unsupported MIME types, and oversize uploads;
- authorize every preview and download request;
- stream private files through a controller after tenant, actor, ownership or staff authority, and file-record checks;
- design an extension point for later malware scanning without blocking the initial repository implementation.

### E. Add secure reference-number generation

Block 3 needs human-friendly but non-enumerable application numbers. Add a tenant-aware reference generator that can produce configured application references while enforcing database uniqueness. Public URLs and APIs must use opaque tokens or ownership-authorized IDs rather than treating sequential database IDs as secrets.

### F. Confirm Block 2 public entry readiness

Before applicant work begins, verify that:

- tenant resolution works on custom-domain and `/t/{tenant}` website routes;
- inactive tenants fail closed;
- the public admissions page can show an active cycle and open programmes from admissions tables;
- public application links preserve resolved tenant context;
- applicant portal pages can use tenant branding without leaking private CMS or media records.

---

# 4. Scope Contract

## 4.1 In Scope

Block 3 includes:

1. admission-cycle setup;
2. cycle status and public-visibility control;
3. programme openings per admission cycle;
4. quotas, screening methods, instructions, and target-entry levels;
5. configurable programme-specific admission requirements;
6. applicant access using email address or Nigerian phone number;
7. Nigerian phone normalization;
8. applicant biodata and contact details;
9. guardian or next-of-kin details where configured;
10. programme selection;
11. O'Level examination sitting and subject-grade capture;
12. required private document uploads;
13. resumable draft applications;
14. server-side completion checks and final submission;
15. tenant-aware application-number generation;
16. admissions review workspace and filters;
17. review notes and private internal comments;
18. document review statuses;
19. screening outcomes;
20. shortlisting, waitlisting, rejection, and offers;
21. admission-list preview and publication;
22. applicant offer viewing and acceptance or decline;
23. clearance-status tracking placeholder;
24. eligible-for-student-conversion marker;
25. admissions dashboard summaries;
26. baseline reports and CSV export;
27. notification event dispatch hooks;
28. admissions audit trail;
29. mobile-first applicant experience;
30. Block 2 public admissions-page integration.

## 4.2 Explicitly Out of Scope

Do not implement the following in Block 3:

- payment gateway integrations;
- invoice generation;
- receipts;
- payment reconciliation;
- ledger entries;
- full application-fee collection;
- full acceptance-fee collection;
- student profile creation or management;
- matriculation-number allocation;
- staff records;
- course allocation;
- course registration;
- result setup, entry, computation, approval, or publication;
- a complete clearance module with multiple institutional units;
- automatic applicant-to-student conversion;
- transcript, hostel, library, biometric, or professional-council integrations;
- tenant-specific admission rules hard-coded in PHP, JavaScript, SQL seeds, or views.

## 4.3 Payment Boundary Decision

Block 3 may store configurable application-fee and acceptance-fee expectations and bounded external-confirmation placeholders so that the admissions lifecycle is ready for Block 4. It must not become a shadow payment engine.

Allowed placeholder fields include:

- `application_fee_required`;
- `application_fee_amount_snapshot`;
- `application_fee_status` with values such as `not_required`, `pending`, `externally_confirmed`, and `waived`;
- `acceptance_fee_required`;
- `acceptance_fee_amount_snapshot`;
- `acceptance_fee_status` with the same bounded values;
- trusted `confirmed_by`, `confirmed_at`, and `confirmation_reference` fields for authorized external/manual confirmation;
- a future `payment_reference_id` nullable integration hook.

Not allowed in Block 3:

- gateway transaction tables;
- payment callbacks;
- receipt numbers;
- reconciliation screens;
- financial reports;
- ledger mutations.

---

# 5. Architectural Decisions

## 5.1 Delivery Pattern

Use server-rendered CI4 pages as the baseline and Vue Composition API enhancements for high-interaction workflows. This keeps the applicant portal resilient on low-bandwidth networks and low-end Android devices while still providing autosave, repeatable O'Level rows, upload progress, retry handling, filterable review lists, and clear inline validation.

Progressive enhancement is mandatory:

- important status information must remain visible without waiting for a large JavaScript bundle;
- final submission and sensitive staff actions must always be validated server-side;
- Vue state is a convenience, never the source of truth;
- failed autosave attempts must be recoverable and clearly communicated;
- duplicate retry requests must not produce duplicate submissions or offers.

## 5.2 Layering Rule

Keep controllers thin:

- **filters** resolve tenant context and enforce coarse authentication or route authority;
- **controllers** parse requests and return HTML, redirects, downloads, or standard API responses;
- **services** enforce lifecycle transitions, applicant ownership, operational scope, validation orchestration, transactions, audit logging, reference generation, and notification dispatch;
- **models** enforce tenant query scope and persistence boundaries;
- **views** render already-authorized data;
- **Vue composables** call narrow API endpoints and render server responses.

No controller may directly perform a multi-record admissions transition such as submission, offer issuance, publication, acceptance, or clearance update.

## 5.3 Tenant Isolation and Related-Record Validation

Every Block 3 tenant-owned table must contain `tenant_id`. Every Block 3 tenant-owned model must extend `TenantScopedModel`. Foreign keys alone are not sufficient because a row could still reference a record owned by another tenant. Services must validate that related records belong to the active tenant before persistence.

Examples:

- a programme opening must reference a cycle, programme, department, and entry level from the active tenant;
- an application must reference an open programme opening from the active tenant and cycle;
- an O'Level sitting, document, review, offer, and acceptance must belong to the active tenant and application;
- a published list entry must reference an offered application included in the same tenant publication.

## 5.4 Applicant Ownership Policy

Add an `ApplicantAccessPolicy` service. It must prove that the authenticated Shield user owns the active tenant applicant profile and requested application before allowing applicant-facing reads or writes. Do not expose application records merely because the route contains a valid ID.

Staff-facing access is different: staff must have active tenant membership, an allowed broad IAM group, required operational authority, and any configured department or programme scope.

## 5.5 Lifecycle State Machines

Lifecycle rules belong in services, not arbitrary controller updates.

### Admission cycle

`draft -> scheduled -> open -> closed -> under_review -> admission_published -> archived`

A service may permit justified transitions such as `scheduled -> draft` before opening or `open -> closed` for an emergency closure, but every transition must be validated and audited.

### Application

`draft -> submitted -> under_review -> screened -> shortlisted|waitlisted|rejected|offered -> accepted|declined -> clearance_pending -> cleared -> eligible_for_student_conversion`

Not every application passes through every state. For example, an application may move from `under_review` to `rejected`, or from `shortlisted` to `waitlisted`. Submitted applications become immutable to the applicant unless an authorized correction window is opened and audited.

### Document review

`pending -> accepted|rejected|replacement_requested`

### Admission list publication

`draft -> previewed -> published -> superseded|archived`

### Acceptance

`pending -> accepted|declined|expired`

### Clearance placeholder

`not_started -> pending -> in_progress -> cleared|not_cleared`

Use explicit transition methods such as `submitApplication()`, `recordScreening()`, `issueOffer()`, `publishAdmissionList()`, `acceptOffer()`, and `markClearanceStatus()` rather than generic public status mutation endpoints.

## 5.6 Data-Driven Configuration Rule

No tenant-specific subject, grade, requirement, programme, quota, instruction, fee expectation, application window, screening rule, letter wording, clearance status label, or admission-list policy may be hard-coded.

The platform may seed neutral defaults such as WAEC, NECO, NABTEB, common O'Level subjects, and grade codes as shared reference data. Tenants must configure the admission requirements and programme openings that actually apply to their school.

## 5.7 Audit and Notification Rule

Every sensitive mutation must record an audit event after successful persistence, inside a consistent transaction boundary where practical. Notifications are event-driven side effects. A notification failure must be logged and retryable; it must not silently reverse a successfully persisted admission decision.

## 5.8 Downstream Student-Conversion Boundary

Block 3 stops after it marks an applicant eligible for student conversion. The eligibility check must be explicit and queryable:

- application is offered;
- offer is accepted;
- required acceptance placeholder status satisfies tenant policy;
- clearance status is `cleared`;
- application has not already been handed off or converted;
- all records belong to the active tenant.

The actual creation of student profile, matriculation number, student login role, and student academic record belongs to Block 5.

---

# 6. Data Model Strategy

## 6.1 Common Tenant-Owned Columns

Every tenant-owned Block 3 table should include the fields applicable to its role:

| Column | Purpose |
| --- | --- |
| `id` | Internal primary key. Never treat it as an authorization secret. |
| `tenant_id` | Mandatory ownership key. Indexed and injected by the tenant-scoped model. |
| `created_by` / `updated_by` | Trusted actor attribution where an authenticated actor performs the mutation. |
| `created_at` / `updated_at` | CI4 timestamp fields. |
| `deleted_at` | Soft-delete timestamp where retention and recovery justify it. |

Mutable lifecycle tables should additionally store dedicated fields such as `submitted_at`, `reviewed_at`, `offered_at`, `published_at`, `accepted_at`, `cleared_at`, and corresponding trusted actor IDs where relevant.

## 6.2 Proposed Admissions Tables

### Configuration tables

| Table | Purpose | Important constraints |
| --- | --- | --- |
| `admission_cycles` | Tenant admission periods tied to academic sessions. | Unique `(tenant_id, code)`; validate tenant session ownership; index public status and dates. |
| `admission_programme_openings` | Programmes offered in a specific cycle. | Unique `(tenant_id, admission_cycle_id, programme_id, entry_level_id)`; validate department, programme, and level ownership. |
| `admission_requirement_definitions` | Configurable biodata, O'Level, and document requirements for a cycle or programme opening. | Index tenant, cycle, opening, requirement type, and status; do not encode school rules in code. |
| `admission_subject_requirements` | Required or optional O'Level subjects and acceptable grades. | Validate referenced shared subject and grade codes; allow programme-specific alternatives and grouping. |
| `admission_document_requirements` | Required document types, MIME policies, and size limits. | Scope to cycle or opening; support required/optional and replacement behavior. |
| `admission_letter_templates` | Tenant-configured offer-letter content and versioning hook. | Tenant scoped; sanitize rendered output; snapshot template version on offer. |

### Applicant and application tables

| Table | Purpose | Important constraints |
| --- | --- | --- |
| `applicant_profiles` | Tenant-specific applicant profile associated with a Shield global user. | Unique `(tenant_id, user_id)`; store normalized contact values and tenant-owned biodata. |
| `applications` | One tenant-owned application record per configured duplicate policy. | Unique application number; validate applicant, cycle, and programme opening; use opaque public token where needed. |
| `application_biodata_snapshots` | Submitted biodata snapshot for review stability. | Immutable after submission except through audited correction workflow. |
| `application_olevel_sittings` | WAEC, NECO, NABTEB, or configured sitting records. | Tenant and application ownership; enforce configured maximum sitting count. |
| `application_olevel_results` | Subject and grade entries within a sitting. | Unique subject per sitting; validate subject and grade reference codes. |
| `application_documents` | Private applicant document metadata. | Tenant, applicant, and application ownership; generated storage key; checksum; review status. |
| `application_correction_windows` | Authorized temporary edit permission after submission. | Opened and closed by trusted actor; reason, scope, timestamps, and audit trail required. |

### Review and decision tables

| Table | Purpose | Important constraints |
| --- | --- | --- |
| `application_reviews` | Staff review outcomes and internal notes. | Staff authority required; private note visibility; record review stage and actor. |
| `application_screenings` | Screening status, method, score or outcome, and notes. | Validate configured screening method and scope; audit every change. |
| `admission_decisions` | Shortlist, waitlist, rejection, or offer decisions. | Preserve decision history; only one current decision; approval authority for sensitive transitions. |
| `admission_offers` | Offer snapshot, programme, template version, issue date, and expiry. | One active offer per application unless superseded through audited workflow. |
| `admission_list_publications` | Versioned tenant list publication batches. | Status-controlled preview and publish workflow; record publisher and timestamp. |
| `admission_list_entries` | Applications included in a publication batch. | Unique publication/application pair; offered applications only. |
| `admission_acceptances` | Applicant response to an offer. | Ownership check; one active response per offer; store accepted/declined timestamp. |
| `admission_clearance_statuses` | Minimal Block 3 clearance placeholder. | Status and note only; full multi-unit clearance belongs later. |
| `student_conversion_eligibilities` | Auditable handoff marker for Block 5. | Unique application; record eligibility timestamp, actor or system source, and downstream conversion placeholder. |

### Reference and support tables

| Table | Purpose | Ownership decision |
| --- | --- | --- |
| `olevel_exam_types` | Neutral WAEC, NECO, NABTEB, and future exam-type reference codes. | Platform shared reference data. |
| `olevel_subjects` | Neutral O'Level subject codes and display names. | Platform shared reference data. |
| `olevel_grades` | Neutral grade codes and ordering or pass semantics. | Platform shared reference data. |
| `admission_reference_sequences` | Tenant-aware application-number sequencing where required. | Tenant owned; lock rows during allocation. |
| `admission_notification_outbox` | Retryable notification intents. | Tenant owned; store channel, event, recipient, payload reference, status, and retry metadata. |

## 6.3 Snapshot Rule

Configuration can change after an application is submitted. Preserve review integrity by snapshotting the applicant's selected programme opening, relevant requirements, fee expectation, submitted biodata, and offer-letter version at the appropriate lifecycle points. Staff reviewing a submitted application must see the rules that applied when it was submitted, not silently changed current rules.

## 6.4 Indexing Priorities

Add composite indexes for common tenant-first queries:

- `(tenant_id, status, opens_at, closes_at)` on cycles;
- `(tenant_id, admission_cycle_id, status, sort_order)` on programme openings;
- `(tenant_id, applicant_profile_id, status)` on applications;
- `(tenant_id, admission_cycle_id, programme_opening_id, status)` on applications;
- `(tenant_id, application_id, review_status)` on documents;
- `(tenant_id, application_id, created_at)` on reviews and decisions;
- `(tenant_id, admission_list_publication_id, sort_order)` on list entries;
- `(tenant_id, status, created_at)` on notification outbox records.

---

# 7. Service and Policy Design

## 7.1 Required Services

| Service | Responsibility |
| --- | --- |
| `AdmissionAuthorityCatalog` | Define and provision admissions operational authorities. |
| `AdmissionCycleService` | Create, update, schedule, open, close, archive, and publicly resolve admission cycles. |
| `AdmissionProgrammeService` | Manage programme openings, quotas, instructions, screening methods, and entry levels. |
| `AdmissionRequirementService` | Manage configurable biodata, O'Level, and private document requirements. |
| `ApplicantIdentityService` | Normalize email or Nigerian phone input, connect authenticated Shield users to tenant applicant profiles, and enforce verified identifier policy. |
| `ApplicantAccessPolicy` | Enforce authenticated user ownership of tenant applicant profiles, applications, documents, offers, and acceptances. |
| `ApplicationDraftService` | Create draft applications and save validated step data idempotently. |
| `OLevelApplicationService` | Save sittings and subject grades and evaluate configured requirements. |
| `ApplicantDocumentService` | Upload, validate, store, download, replace, and review private documents. |
| `ApplicationSubmissionService` | Run completion checks, snapshot rules, allocate reference number, lock submission, audit, and dispatch a notification event. |
| `AdmissionReviewService` | Provide tenant-scoped staff queries, review detail views, notes, document reviews, and correction windows. |
| `ScreeningService` | Record screening methods, statuses, scores where configured, and shortlisting recommendations. |
| `AdmissionDecisionService` | Shortlist, waitlist, reject, approve, issue, supersede, and audit decisions and offers. |
| `AdmissionPublicationService` | Build list previews, publish versioned lists, expose allowed public list data, and archive or supersede versions. |
| `AdmissionAcceptanceService` | Allow the owning applicant to accept or decline an active offer and track bounded acceptance-fee placeholder status. |
| `AdmissionClearanceService` | Track minimal clearance status and compute student-conversion eligibility. |
| `AdmissionReferenceGenerator` | Produce tenant-aware application references safely and uniquely. |
| `AdmissionDashboardService` | Return tenant pipeline metrics and setup gaps. |
| `AdmissionReportService` | Produce scoped reports and CSV exports. |
| `AdmissionNotificationDispatcher` | Persist notification intents and dispatch channel-specific jobs with retry behavior. |

## 7.2 Transaction Boundaries

Use database transactions for multi-record changes:

- final application submission;
- application reference allocation;
- correction-window open or close;
- document replacement metadata change;
- screening update with history;
- decision issue or supersede;
- admission-list publication and entries;
- offer acceptance or decline;
- clearance update and eligibility marker creation.

Audit records for successful sensitive mutations should be written consistently with the business change. Notification dispatch should use an outbox-style handoff so a temporary SMS or email failure does not corrupt the admission state.

## 7.3 Idempotency

Mobile users may retry requests after network interruption. Sensitive endpoints must tolerate retry safely:

- autosave updates the same draft step rather than creating duplicates;
- final submission returns the already-submitted application when the same draft is retried;
- accepting an already-accepted offer returns the existing accepted state;
- publication requires a stable preview batch or idempotency key;
- offer issuance prevents duplicate active offers;
- uploaded replacement files are associated through a server-issued upload intent or deterministic replacement workflow.

---

# 8. Authentication, Authorization, and Security Plan

## 8.1 Route Protection Layers

### Public discovery routes

Require resolved, active public tenant context. They expose only explicitly public cycles, open programmes, sanitized requirements, and published list fields.

### Applicant routes

Require:

1. resolved active tenant context;
2. Shield authentication;
3. applicant profile for the active tenant;
4. applicant ownership policy;
5. lifecycle permission;
6. CSRF protection for browser mutations;
7. validation and audit logging for sensitive actions.

### Tenant staff routes

Require:

1. Shield authentication;
2. resolved tenant context;
3. active tenant membership;
4. allowed broad IAM group;
5. admissions operational authority;
6. configured department or programme scope where relevant;
7. CSRF protection for browser mutations;
8. service-layer validation and audit logging.

## 8.2 Nigerian Phone Normalization

Normalize Nigerian mobile identifiers to a canonical form such as E.164 `+234...` before identity lookup. Accept carefully validated user input variants such as `080...`, `234...`, and `+234...`, but store and compare one canonical normalized value. Do not assume every applicant checks email frequently. Provide phone-first UX where configured while still supporting email.

## 8.3 Verification, Recovery, and Abuse Controls

Use Shield-compatible verification and recovery flows. At minimum:

- verify a reachable email address or phone number before sensitive applicant progression according to platform policy;
- throttle registration, login, OTP or verification attempts, resend actions, password reset, upload, and submission endpoints;
- avoid revealing whether a global identity exists during recovery;
- regenerate sessions after login;
- log suspicious repeated failures;
- never place credentials, OTPs, private notes, or sensitive biodata in audit metadata.

SMS delivery integration may be implemented as a notification-channel adapter or placeholder if no provider is selected. The provider must remain configurable and tenant-independent.

## 8.4 Private Document Security

Applicant document endpoints must:

- deny public access by default;
- require an authorized applicant owner or authorized tenant staff reviewer;
- validate the file record belongs to the active tenant and requested application;
- emit safe content-disposition headers;
- avoid using original filenames as storage keys;
- enforce MIME, extension, and size policy server-side;
- store checksum and upload metadata;
- log downloads where policy requires it;
- exclude private files from public caches.

## 8.5 Data Minimization

Collect only configurable biodata required for admissions. Avoid collecting sensitive fields merely because they may be useful later in student records. Block 5 may extend student data after conversion. Public admission lists must reveal only tenant-configured safe fields, such as application number and applicant display name policy; they must never expose phone numbers, email addresses, document links, or internal review notes.

---

# 9. Routes, Controllers, and Pages

## 9.1 Suggested Public Routes

| Method | Route | Purpose |
| --- | --- | --- |
| `GET` | `/admissions` | Existing tenant public admissions page enhanced with active-cycle and open-programme data. |
| `GET` | `/admissions/programmes` | Public open-programme list for the resolved tenant. |
| `GET` | `/admissions/programmes/{slug}` | Sanitized programme-opening details and requirements. |
| `GET` | `/admissions/lists` | Published admission-list index where enabled. |
| `GET` | `/admissions/lists/{publicToken}` | Published list details with tenant-configured safe fields only. |
| `GET` | `/apply` | Tenant-resolved applicant start page. |

Equivalent `/t/{tenant}/...` aliases may be supported consistently with Block 2 routing.

## 9.2 Suggested Applicant Portal Routes

Use a tenant-resolved namespace such as `/applicant`:

| Method | Route | Purpose |
| --- | --- | --- |
| `GET` | `/applicant` | Applicant dashboard and immediate admission status. |
| `GET/POST` | `/applicant/profile` | View or update tenant applicant biodata draft. |
| `POST` | `/applicant/applications` | Start an application for an open programme. |
| `GET` | `/applicant/applications/{token}` | Resume application. |
| `PUT` | `/applicant/applications/{token}/biodata` | Save biodata step. |
| `PUT` | `/applicant/applications/{token}/olevel` | Save O'Level step. |
| `POST` | `/applicant/applications/{token}/documents` | Upload a required private document. |
| `DELETE` | `/applicant/applications/{token}/documents/{documentToken}` | Remove allowed draft document. |
| `GET` | `/applicant/applications/{token}/preview` | Show completion and validation preview. |
| `POST` | `/applicant/applications/{token}/submit` | Submit idempotently after server validation. |
| `GET` | `/applicant/applications/{token}/offer` | View active offer and admission letter. |
| `POST` | `/applicant/applications/{token}/offer/accept` | Accept active offer. |
| `POST` | `/applicant/applications/{token}/offer/decline` | Decline active offer. |
| `GET` | `/applicant/documents/{documentToken}` | Authorized private document download or preview. |

Applicant authentication routes should be implemented through configured Shield actions and tenant-aware redirect handling rather than ad hoc password logic.

## 9.3 Suggested Tenant Staff Routes

Use a tenant-admin namespace such as `/tenant/admissions`:

- dashboard;
- cycles list, create, edit, open, close, archive;
- programme-opening list, create, edit, status, quota;
- requirement list, create, edit, reorder, activate, archive;
- application list and detail;
- review note and correction-window actions;
- document accept, reject, and replacement-request actions;
- screening update;
- shortlist, waitlist, reject, offer, and approval actions;
- admission-list preview, publish, supersede, archive, and export actions;
- acceptance list;
- clearance-status update;
- reports;
- audit view.

Apply the narrowest practical operational-authority filter to each route group. Services must repeat decisive checks because filters alone cannot validate record ownership, scope, lifecycle, or relationships.

## 9.4 Required Controllers

Suggested controller namespaces:

```text
app/Controllers/
├── PublicSite/Admissions/
│   ├── AdmissionProgrammeController.php
│   └── AdmissionListController.php
├── Applicant/
│   ├── DashboardController.php
│   ├── ProfileController.php
│   ├── ApplicationController.php
│   ├── DocumentController.php
│   ├── SubmissionController.php
│   └── OfferController.php
└── Tenant/Admissions/
    ├── DashboardController.php
    ├── CycleController.php
    ├── ProgrammeOpeningController.php
    ├── RequirementController.php
    ├── ApplicationReviewController.php
    ├── DocumentReviewController.php
    ├── ScreeningController.php
    ├── DecisionController.php
    ├── PublicationController.php
    ├── AcceptanceController.php
    ├── ClearanceController.php
    ├── ReportController.php
    └── AuditController.php
```

## 9.5 Applicant Pages

Required mobile-first pages:

1. start or continue application;
2. register, login, verify, and recover access;
3. applicant dashboard;
4. profile and biodata;
5. select programme;
6. O'Level sittings and grades;
7. document upload and retry;
8. application preview and validation summary;
9. submission confirmation and application number;
10. current application status;
11. offer and admission letter;
12. accept or decline offer;
13. clearance-status tracker.

## 9.6 Tenant Staff Pages

Required staff pages:

1. admissions dashboard;
2. cycle setup;
3. programme openings;
4. requirements manager;
5. application list and filters;
6. application detail review;
7. document review;
8. screening and shortlisting;
9. decision and offer workspace;
10. admission-list preview and publication;
11. acceptance tracker;
12. clearance-status tracker;
13. reports and CSV exports;
14. admissions audit view.

---

# 10. Vue Composition API Plan

Use Vue only where it materially improves usability.

## 10.1 Applicant Enhancements

Recommended composables and components:

- `useApplicationDraft()` for debounced autosave, retry, dirty-state display, and server validation errors;
- `useProgrammeOpenings()` for cycle-aware programme selection;
- `useOLevelEntries()` for repeatable sittings and subject-grade rows;
- `useDocumentUploads()` for type checks, progress, retry, replacement, and clear failure messages;
- `useApplicationPreview()` for completion status and missing requirements;
- `useOfferResponse()` for confirmation and idempotent accept or decline.

## 10.2 Tenant Staff Enhancements

Recommended enhancements:

- cycle and programme-opening configuration forms;
- requirement builder with ordering and conditional scope;
- filterable application review workspace;
- document-review panel;
- screening and decision action forms;
- publication preview;
- dashboard metrics refresh.

## 10.3 Frontend Rules

- Keep initial pages server-rendered.
- Compile assets through the existing Vite path.
- Use small entry bundles per workspace rather than one oversized admissions bundle.
- Preserve CSRF handling on every mutation.
- Render field-level and summary-level errors.
- Never trust frontend status checks as authorization.
- Avoid storing private biodata in browser storage unless explicitly reviewed and encrypted appropriately; prefer server drafts.

---

# 11. Validation and Business Rules

## 11.1 Admission Cycle Rules

- cycle code is unique within a tenant;
- academic session belongs to the active tenant;
- closing time follows opening time in tenant timezone;
- only valid lifecycle transitions are accepted;
- public discovery returns only visible cycles within allowed states;
- application submission fails closed after closure even if the browser loaded the form earlier.

## 11.2 Programme Opening Rules

- referenced cycle, programme, department, and level belong to the active tenant;
- only active programmes can be opened;
- hidden, closed, suspended, or full openings cannot accept new submissions;
- quotas are configuration-driven;
- quota behavior must distinguish warning, hard stop, and staff override policy;
- any override requires authority, reason, and audit event.

## 11.3 Applicant Rules

- email is normalized safely;
- Nigerian phone number is normalized to one canonical format;
- duplicate tenant applicant profile is prevented for the same global user;
- applicant must not access another applicant's application by URL manipulation;
- applicant must not edit submitted data without an authorized correction window;
- applicant-facing validation messages are concise and understandable.

## 11.4 O'Level Rules

- exam type is drawn from shared references or permitted configured extension;
- exam year and number fields follow configured validation;
- subject is unique within a sitting;
- grade belongs to allowed reference grades;
- maximum sitting count is enforced per configured requirement;
- subject requirement evaluation is programme-opening specific;
- alternative-subject groups are supported where configured;
- tenant staff sees the evaluation summary and underlying entries.

## 11.5 Document Rules

- requirement applies to the selected cycle or programme opening;
- required documents are present before submission unless tenant policy explicitly allows later completion;
- MIME type, extension, and size checks are server-side;
- generated storage key stays private;
- applicant can replace documents only while lifecycle permits it;
- document reviewer status changes are audited;
- rejected documents can trigger a replacement-request notification.

## 11.6 Submission Rules

- application belongs to the authenticated applicant in the active tenant;
- cycle is open at transaction time;
- programme opening accepts submissions at transaction time;
- duplicate policy is satisfied;
- required biodata is complete;
- configured O'Level rule evaluation passes or is explicitly allowed to submit for manual review;
- required document rule is satisfied;
- fee placeholder rule is satisfied where configured;
- application number is allocated once;
- submission snapshots are immutable;
- retry is idempotent.

## 11.7 Review, Decision, and Publication Rules

- reviewer has active membership and scoped authority;
- private comments are never exposed to applicants or public pages;
- screening method follows configured programme-opening policy;
- decision transition is valid;
- offer references an eligible application and captures the offered programme and letter-template version;
- publication preview contains offered applications only;
- publication action requires publish authority;
- public list exposes only tenant-configured safe fields;
- offer acceptance belongs to the owning applicant and active offer;
- clearance update requires clearance authority;
- eligibility marker is created only after accepted and cleared conditions pass.

---

# 12. Audit and Notification Plan

## 12.1 Required Audit Events

At minimum audit:

### Configuration

- `admissions.cycle.created`
- `admissions.cycle.updated`
- `admissions.cycle.opened`
- `admissions.cycle.closed`
- `admissions.cycle.archived`
- `admissions.programme.opened`
- `admissions.programme.updated`
- `admissions.programme.status_changed`
- `admissions.requirement.created`
- `admissions.requirement.updated`
- `admissions.requirement.archived`

### Applicant

- `admissions.applicant.profile_created`
- `admissions.applicant.profile_updated`
- `admissions.application.started`
- `admissions.application.draft_saved`
- `admissions.document.uploaded`
- `admissions.document.replaced`
- `admissions.document.deleted`
- `admissions.application.submitted`
- `admissions.offer.accepted`
- `admissions.offer.declined`

### Staff workflow

- `admissions.application.reviewed`
- `admissions.application.correction_window_opened`
- `admissions.application.correction_window_closed`
- `admissions.document.review_status_changed`
- `admissions.screening.updated`
- `admissions.application.shortlisted`
- `admissions.application.waitlisted`
- `admissions.application.rejected`
- `admissions.offer.issued`
- `admissions.offer.superseded`
- `admissions.list.previewed`
- `admissions.list.published`
- `admissions.list.superseded`
- `admissions.clearance.status_changed`
- `admissions.student_conversion.eligibility_marked`

Avoid noisy autosave audits where they would obscure important actions. If draft autosave is recorded, summarize it without storing sensitive form contents.

## 12.2 Notification Events

Queue notification intents for:

- account verification;
- application draft reminder where enabled;
- successful submission;
- replacement document request;
- screening schedule or outcome where configured;
- shortlist, waitlist, rejection, or offer;
- admission-list publication where configured;
- offer acceptance confirmation;
- clearance-status change;
- upcoming offer expiry.

Support email and SMS adapters without hard-coding a provider. SMS wording must be concise and safe: do not include sensitive data unnecessarily.

---

# 13. Reporting and Dashboard Plan

## 13.1 Tenant Admissions Dashboard

Show tenant-scoped metrics:

- active cycle;
- open programmes;
- setup gaps;
- started drafts;
- submitted applications;
- applications by programme;
- document-review backlog;
- screening summary;
- shortlisted, waitlisted, rejected, and offered totals;
- publication status;
- accepted and declined totals;
- clearance summary;
- student-conversion eligible total;
- quota utilization.

## 13.2 Required Reports

Provide scoped screens and CSV export for:

- applicant list;
- programme application summary;
- submitted versus draft applications;
- screening list;
- shortlist;
- waitlist;
- rejection list;
- admission-offer list;
- published admission list;
- acceptance list;
- clearance-status list;
- student-conversion eligibility list;
- quota utilization;
- admissions audit report.

Every report must enforce tenant scope, staff authority, and any department or programme scope. Exports must avoid unnecessary sensitive fields and log export actions where policy requires it.

---

# 14. Mobile-First and Nigeria-Aware UI Plan

## 14.1 Applicant Priorities

- Optimize for narrow screens first.
- Use a step-based form with a visible progress indicator.
- Save each step as a server-side draft.
- Allow pause and resume after unstable connectivity.
- Use touch-friendly controls and appropriate mobile keyboard input modes.
- Make Nigerian phone input clear and normalize it server-side.
- Keep O'Level sittings and subjects simple to add, edit, and remove.
- Explain file type and maximum size before upload.
- Show upload progress, failure, and retry state.
- Display the current application and admission status immediately after login.
- Avoid long tables; render cards or stacked rows on small screens.
- Keep critical status pages useful with limited JavaScript.

## 14.2 Staff Priorities

- Make review queues filterable without requiring desktop-only interactions.
- Collapse wide records into labeled cards on narrow screens.
- Keep decision actions explicit and confirmation-protected.
- Surface programme, application number, lifecycle status, and pending actions prominently.
- Avoid placing private notes beside public-facing notes without a clear visual distinction.
- Provide CSV exports for heavy offline analysis rather than attempting a complex analytics UI in Block 3.

## 14.3 Performance and Resilience

- Keep public admissions discovery cacheable by tenant and invalidate on relevant configuration or publication changes.
- Never cache applicant dashboards, private documents, or staff pages publicly.
- Paginate staff review queues.
- Defer non-critical assets.
- Keep upload limits realistic for mobile data use.
- Use concise HTML responses and focused JavaScript bundles.

---

# 15. Phased Implementation Work Plan

## Phase 0 — Identity, Security, and Admission Readiness

**Goal:** close prerequisites before domain feature work.

Deliverables:

- install and configure Shield;
- add Shield-compatible email and Nigerian-phone identity normalization;
- verify tenant-aware applicant start flow;
- provision admissions authorities;
- add applicant route filters and ownership policy skeleton;
- add admissions configuration class for neutral defaults and upload policy;
- add private applicant-document storage foundation;
- add admissions reference generator skeleton;
- add test helpers for tenant, applicant, and staff contexts.

Exit gate:

- authenticated applicant and staff test contexts work;
- tenant A cannot resolve tenant B applicant data;
- private document request fails closed without correct ownership or staff authority;
- Shield, tenant context, and CSRF strategy are documented and exercised.

## Phase 1 — Admission Configuration Foundation

**Goal:** allow a tenant to open admissions without hard-coded school rules.

Deliverables:

- cycle migration, model, service, controller, views, APIs, and audit events;
- programme-opening migration, model, service, controller, views, APIs, and audit events;
- requirement-definition, subject-requirement, and document-requirement persistence;
- tenant-admin requirements manager;
- public active-cycle and open-programme queries;
- Block 2 admissions-page integration;
- setup-gap dashboard summary.

Exit gate:

- tenant can create, open, close, and archive a cycle;
- tenant can expose selected programmes only;
- tenant can configure requirements per opening;
- public discovery reveals only active tenant public data.

## Phase 2 — Applicant Access and Mobile Draft Foundation

**Goal:** let prospective applicants securely start and resume a tenant-owned application.

Deliverables:

- tenant applicant profile model and service;
- Shield-based applicant registration, login, verification, recovery, and tenant-aware redirects;
- applicant dashboard;
- draft application creation;
- programme selection;
- mobile-first profile and biodata step;
- Vue autosave composable with retry;
- ownership-policy tests.

Exit gate:

- applicant can use email address or normalized Nigerian phone number;
- applicant can start and resume own draft only;
- applicant sees tenant branding and current status;
- tenant and ownership ID manipulation fails closed.

## Phase 3 — O'Level, Documents, Preview, and Submission

**Goal:** complete the application journey.

Deliverables:

- shared exam-type, subject, and grade references;
- O'Level sitting and result services;
- repeatable Vue O'Level editor;
- private document upload, replacement, retry, and authorized download;
- completion evaluator;
- preview page;
- idempotent final submission transaction;
- application-number allocation;
- submitted snapshots;
- submission confirmation and notification event.

Exit gate:

- applicant can complete biodata, O'Level, and required documents;
- server rejects incomplete, invalid, closed-cycle, and wrong-tenant submissions;
- valid retry produces one submitted application and one application reference.

## Phase 4 — Review, Documents, and Screening Workspace

**Goal:** let authorized admissions staff process submitted applications.

Deliverables:

- scoped review queue and filters;
- application review detail;
- document-review actions;
- private internal notes;
- correction-window service;
- screening records and outcome actions;
- staff dashboard metrics;
- audit view foundation.

Exit gate:

- admission officer can review only authorized tenant and operational scope;
- private comments stay private;
- correction access is temporary, explicit, and audited;
- document and screening updates are validated and audited.

## Phase 5 — Shortlisting, Decisions, and Offers

**Goal:** support controlled admission decisions.

Deliverables:

- shortlist, waitlist, rejection, and offer transitions;
- approval policy hook for offer issuance where configured;
- offer snapshots and expiry;
- tenant offer-letter template hook;
- applicant offer page;
- decision notification events;
- decision audit history.

Exit gate:

- only authorized staff can make scoped decisions;
- one current decision and one active offer rule are enforced;
- applicant sees only own offer;
- offer mutations are auditable and retry-safe.

## Phase 6 — Admission List Publication

**Goal:** publish safe, versioned admission lists.

Deliverables:

- publication batch and entry tables;
- preview workflow;
- publish-authority enforcement;
- public list page with safe configured fields;
- applicant portal visibility;
- CSV or PDF export hook where appropriate;
- cache invalidation;
- publication audit and notification events.

Exit gate:

- unpublished lists remain private;
- published list contains offered applicants from the active tenant only;
- public output excludes sensitive applicant fields;
- repeated publish requests do not duplicate publication state.

## Phase 7 — Acceptance, Clearance Placeholder, and Handoff

**Goal:** finish the Block 3 applicant-to-admission boundary.

Deliverables:

- applicant offer accept or decline flow;
- bounded acceptance-fee status placeholder;
- clearance status placeholder and staff tracker;
- conversion-eligibility calculation;
- student-conversion eligibility marker;
- accepted, cleared, and eligible reports;
- notification and audit events.

Exit gate:

- applicant can accept or decline own active offer;
- authorized staff can track minimal clearance status;
- eligible marker is created only for accepted and cleared applicants satisfying configured placeholder rules;
- no student record is created in Block 3.

## Phase 8 — Reports, Operations, and Stabilization

**Goal:** prove the block is safe and operable.

Deliverables:

- admissions reports and CSV exports;
- dashboard finalization;
- outbox retries and operational logs;
- full tenant-isolation suite;
- applicant ownership suite;
- authority and scope suite;
- lifecycle and idempotency suite;
- upload security suite;
- manual mobile verification;
- public website integration verification;
- performance review for review queues and mobile bundles.

Exit gate:

- all Definition of Done items pass;
- Block 4 and Block 5 integration hooks remain bounded and documented;
- no later-block feature has leaked into the implementation.

---

# 16. Testing Strategy

## 16.1 Automated Test Categories

### Tenant isolation

- tenant A cycles, openings, requirements, applicants, applications, documents, offers, publications, acceptances, and clearance statuses never appear in tenant B queries;
- related-record injection across tenants fails validation;
- direct ID and token manipulation fails closed;
- public list route cannot expose another tenant's publication.

### Applicant identity and ownership

- email login works through Shield policy;
- valid Nigerian phone variants normalize consistently;
- malformed phone values fail validation;
- applicant can access only own tenant profile and applications;
- one global user may have separate tenant applicant profiles only when explicitly allowed by product policy;
- applicant cannot access staff routes;
- applicant cannot edit submitted data without correction window.

### Staff authorization and scope

- unauthenticated staff routes fail;
- inactive membership fails;
- missing admissions authority fails;
- department or programme scoped officer cannot review outside assigned scope;
- publication requires publish authority;
- clearance update requires clearance authority;
- UI hiding is not required for route denial to work.

### Configuration and public discovery

- only active tenant academic records can be referenced;
- cycle dates and lifecycle transitions validate;
- closed or hidden opening does not appear publicly;
- inactive tenant public admissions page fails closed;
- no tenant-specific content is hard-coded into public output.

### Draft and submission

- each step saves and resumes correctly;
- autosave retry is idempotent;
- required biodata, O'Level, and document rules are enforced;
- sitting count and subject uniqueness rules work;
- closed-cycle submission fails at transaction time;
- duplicate application policy works;
- final submission generates one tenant reference and immutable snapshot.

### Private documents

- invalid MIME type and oversize upload fail;
- arbitrary storage paths cannot be supplied;
- traversal attempt fails;
- private document lacks public URL;
- applicant owner can download own allowed document;
- other applicant and other tenant cannot download it;
- authorized reviewer can download within operational scope;
- replacement and rejection actions audit correctly.

### Review, decision, and publication lifecycle

- invalid state transition fails;
- private note never appears in applicant or public response;
- correction window opens and closes correctly;
- duplicate active offer is prevented;
- acceptance retry is idempotent;
- publication contains eligible offered applicants only;
- published list leaks no private contacts or documents;
- superseded publication remains historically auditable.

### Clearance and downstream boundary

- acceptance alone does not create student profile;
- clearance alone does not create student profile;
- accepted and cleared applicant receives one eligibility marker;
- repeated eligibility calculation remains idempotent;
- Block 3 schema does not implement student profile management.

### Audit and notifications

- sensitive mutations create expected tenant-scoped audit events;
- metadata excludes secrets and unnecessary biodata;
- notification intent is persisted;
- channel failure can retry without reversing business state;
- cross-tenant audit visibility fails closed.

## 16.2 Manual Verification Checklist

Verify manually on a narrow mobile viewport and a desktop viewport:

1. enter from tenant public admissions page;
2. start with email address;
3. start with Nigerian phone number;
4. pause and resume a draft;
5. add multiple O'Level sittings;
6. upload, fail, and retry a document;
7. preview missing requirements;
8. submit and see application number;
9. review as authorized admission officer;
10. confirm unauthorized staff denial;
11. issue and view an offer;
12. publish a safe admission list;
13. accept offer as applicant;
14. update clearance placeholder as authorized staff;
15. confirm student-conversion eligibility marker without student creation;
16. inspect audit log and notification outbox;
17. verify no public page exposes private files, private notes, phone numbers, or email addresses.

---

# 17. Definition of Done

Block 3 is complete only when:

1. Shield is installed, configured, and used as the identity foundation.
2. Tenant can create, open, close, and archive an admission cycle.
3. Tenant can open selected configured programmes for application.
4. Tenant can configure programme-specific biodata, O'Level, and document requirements without code changes.
5. Public website shows active tenant admission information and open programmes only.
6. Applicant can start from the resolved tenant website.
7. Applicant can register or access the portal using an email address or normalized Nigerian phone number.
8. Applicant can pause, resume, and complete biodata, O'Level, and document steps on a mobile device.
9. Applicant documents remain private and ownership-authorized.
10. Final submission validates current cycle, programme, duplicate, requirements, and placeholder policies server-side.
11. Final submission generates one tenant-aware application number and immutable review snapshot.
12. Admission officer can review only authorized tenant and operational scope.
13. Document review, screening, shortlisting, waitlisting, rejection, and offer workflows work through validated transitions.
14. Authorized staff can preview and publish a safe versioned admission list.
15. Applicant can view and accept or decline only their own active offer.
16. Authorized staff can track minimal clearance status.
17. Accepted and cleared applicant can be marked eligible for Block 5 student conversion.
18. No Block 3 action creates or manages a student profile.
19. No payment gateway, ledger, receipt, reconciliation, course, result, or staff-record engine is implemented.
20. Every admissions record is tenant-scoped.
21. Every sensitive action enforces authentication, tenant context, authorization or applicant ownership, validation, lifecycle policy, and audit logging.
22. Sensitive retries are idempotent.
23. Applicant pages are mobile-first, low-bandwidth conscious, and Nigeria-aware.
24. No tenant-specific admission data is hard-coded.
25. Automated and manual verification cover tenant isolation, ownership, authority scope, private uploads, lifecycle, audit, and downstream boundaries.

---

# 18. Final Block 3 Delivery Summary

Block 3 delivers the applicant-to-admission foundation of the SaaS SHST platform.

After Block 3, a tenant school can:

- open admission;
- expose configured programmes and requirements;
- receive resumable mobile applications;
- review applicants and documents;
- record screening outcomes;
- shortlist, waitlist, reject, or offer admission;
- publish a safe admission list;
- track offer acceptance;
- track a minimal clearance status;
- mark accepted and cleared applicants as eligible for student conversion.

The implementation remains deliberately bounded. Block 4 can add the full payment engine without rewriting admissions. Block 5 can consume the student-conversion eligibility handoff without moving student profile management into Block 3. Once this block is stable, the next block can safely begin.

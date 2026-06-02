# Block 3 Phase 1 — Admission Configuration Foundation

## Objective

Phase 1 allows an authorized tenant school to configure and publicly expose an admission cycle without hard-coded school rules. It does not create applicant drafts or process applications.

## Delivered Configuration

- `admission_cycles` ties tenant admission windows to tenant academic sessions and validates lifecycle transitions.
- `admission_programme_openings` selects active tenant programmes, their department relationship, optional entry level, quota, screening method, status, instructions, and requirement summary.
- `admission_requirement_definitions` stores configurable biodata, O'Level, document, and other rules.
- `admission_subject_requirements` stores tenant-configured O'Level subjects, minimum grades, and alternative groups.
- `admission_document_requirements` stores private-upload requirements bounded by the neutral platform upload policy.

All records contain `tenant_id`, use tenant-scoped models, validate related records through services, and emit audit events after successful configuration changes.

## Public Discovery

The existing Block 2 `/admissions` page now includes the resolved tenant's active public admission cycle and open programmes. Read-only `/admissions/programmes` and `/admissions/programmes/{id}` pages show sanitized active configuration only. Hidden, closed, full, suspended, inactive, wrong-tenant, future, expired, or non-public records do not appear.

## Tenant Administration

Authorized staff use:

- `/tenant/admissions`
- `/tenant/admissions/cycles`
- `/tenant/admissions/programmes`
- `/tenant/admissions/requirements`

Routes enforce Shield authentication, tenant context, membership, narrow operational authority, and CSRF protection for browser mutations. Services repeat decisive authority checks and tenant relationship validation.

## Phase Boundary

Phase 1 intentionally does not implement applicant draft creation, biodata entry, O'Level entry, document upload, submission, review, screening outcomes, offers, payments, or student conversion.

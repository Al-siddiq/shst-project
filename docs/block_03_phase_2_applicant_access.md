# Block 3 Phase 2 — Applicant Access and Mobile Draft Foundation

## Objective

Phase 2 lets an authenticated prospective applicant create or reuse a tenant applicant profile, start a tenant-owned draft application for an open programme, resume only their own draft, and save the biodata step as a server-side draft.

## Delivered Applicant Access

- Applicant profile creation uses the Shield-authenticated user ID and the active tenant context.
- Applicant identifiers are normalized through the Phase 0 email/Nigerian-phone normalizer.
- `/apply` now shows tenant-aware Shield login/register links and open programmes.
- Applicant-only draft routes require Shield authentication, tenant context, and an owned active applicant profile.
- Applicant profile creation does not require `applicantAccess` because that route is how an authenticated user creates the tenant applicant profile.

## Delivered Draft Foundation

- `applicant_applications` stores tenant-owned draft application shells for selected open programme openings.
- `application_biodata_drafts` stores server-side biodata draft fields and completion progress.
- Drafts use opaque public tokens for applicant resume routes.
- Starting the same programme again resumes the existing draft instead of creating duplicates.
- Biodata autosave normalizes Nigerian phone values and email values before persistence.

## Vue Composition API

The biodata page remains server-rendered and works with normal form submission. `resources/js/applicant-draft.js` adds a small Composition API autosave island with retry messaging for mobile users on unstable networks.

## Phase Boundary

Phase 2 intentionally does not implement O'Level entry, document upload persistence, application preview, final submission, application numbers, review, screening, offers, payments, or student conversion.

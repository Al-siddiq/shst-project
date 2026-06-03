# Block 3 Phase 3 — O'Level, Documents, Preview, and Submission

Phase 3 completes the applicant-side submission journey while keeping review, payments, student conversion, and staff workflows out of scope.

## Delivered scope

- Shared neutral O'Level references for Nigerian admissions contexts: WAEC, NECO, NABTEB, common O'Level subjects, and grade codes.
- Tenant-scoped O'Level sittings and subject-grade records attached to applicant applications.
- Private document upload and replacement beneath `WRITEPATH/uploads/admissions` with MIME, size, checksum, and metadata validation.
- Server-side completion evaluation for biodata, O'Level records, open cycle/programme state, and configured required documents.
- Applicant preview route and view before final submission.
- Idempotent final submission transaction with tenant-scoped application number allocation, immutable submitted snapshot, audit log entry, and notification outbox intent.

## Boundaries intentionally preserved

- No payment engine is implemented.
- No admissions review, screening, shortlisting, offer, list publication, acceptance, clearance, or student conversion workflow is implemented in this phase.
- No tenant-specific O'Level or document rule is hard-coded; tenants continue to configure programme requirements through Phase 1 records.

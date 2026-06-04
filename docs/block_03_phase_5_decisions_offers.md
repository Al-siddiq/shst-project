# Block 3 Phase 5 — Shortlisting, Decisions, and Offers

Phase 5 adds controlled admissions decisions after review/screening while keeping publication, acceptance, clearance, payments, and student conversion out of scope.

## Delivered scope

- Shortlist, waitlist, and rejection batches with tenant-scoped entries.
- Current admission decision records for shortlisted, waitlisted, rejected, and offered outcomes.
- Approval policy hook for offer decisions requiring `admissions.decisions.approve` before offer issuance.
- Retry-safe offer issuance with active-offer checks, offer reference generation, snapshots, expiry, and template-key hook.
- Applicant-facing offer page that exposes only the authenticated applicant's own active offer.
- Decision and offer audit events plus notification outbox events.

## Boundaries intentionally preserved

- No admission list publication or public list versioning is implemented.
- No offer acceptance/decline, clearance, payment, or student conversion action is implemented.
- Offer-letter rendering is represented by a template key and snapshot hook only; full document generation remains future work.

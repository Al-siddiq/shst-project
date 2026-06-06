# Block 3 Phase 6 — Admission List Publication

Phase 6 adds safe, versioned admission-list publication for already offered applicants.

## Delivered scope

- Tenant-scoped publication headers and safe public entry tables.
- Staff preview workspace protected by `admissions.lists.preview`.
- Publish action protected by `admissions.lists.publish`.
- Validation that each list entry references an active offer from the same tenant cycle and optional programme scope.
- Public admission-list index/detail pages that expose only application number, display name, and programme.
- Applicant dashboard visibility when the applicant appears on a published list.
- Cache invalidation, publication audit event, notification outbox event, and CSV/PDF export hook metadata.

## Boundaries intentionally preserved

- No offer acceptance/decline flow is implemented.
- No clearance, payment, or student conversion workflow is implemented.
- Public output excludes contact details, documents, O'Level rows, screening scores, private notes, and internal decisions.

# Block 3 Phase 8 — Reports, Operations, and Stabilization

Phase 8 finalizes Block 3 as an operable admissions foundation for tenant schools.

## Objective

Provide safe tenant-scoped reporting, CSV exports, dashboard finalization, notification outbox retry visibility, admissions audit visibility, and stabilization checks for the complete applicant-to-admission flow delivered in Block 3.

## Delivered Scope

- Tenant admissions reports for application pipeline, decisions/offers, acceptance/clearance, and admission-list publication.
- Safe CSV exports that omit private reviewer notes, private document paths, payment internals, and student-conversion records.
- Dashboard operational metrics for pending and failed notification intents.
- Operations workspace for tenant-scoped notification outbox retry scheduling and admissions audit visibility.
- Policy and contract tests covering Phase 8 routes, services, tenant scoping, and later-block boundaries.

## Security and Tenant Rules

- Report and export routes require authentication, resolved tenant context, tenant membership, and `admissions.reports.view` authority.
- Operations routes require authentication, resolved tenant context, tenant membership, and `admissions.audit.view` authority.
- Report data uses tenant-scoped models; audit logs are explicitly filtered by the resolved `tenant_id` because audit logs are platform-wide.
- Outbox retry does not send messages directly. It only re-queues existing tenant-owned notification intents for a future delivery worker.

## Stabilization Checklist

- Tenant-isolation suite: every Phase 8 data source is tenant-scoped or explicitly filtered by `tenant_id`.
- Applicant-ownership suite: applicant documents and applicant-owned offer actions remain protected by existing applicant ownership policy.
- Authority and scope suite: reports and operations are split between `admissions.reports.view` and `admissions.audit.view`.
- Lifecycle and idempotency suite: retrying an outbox item is a requeue operation and does not create duplicate applications, offers, lists, clearances, or student records.
- Upload security suite: Phase 8 never exposes private document paths and does not add new upload surfaces.
- Manual mobile verification: applicant-facing pages from earlier phases should still be checked on a small viewport after reports/operations routing is deployed.
- Public website integration verification: public admissions and published-list pages remain anonymous and safe; staff reports are not publicly routed.
- Performance review: review queues and report exports are capped to 1,000 rows per export and should be paginated before production-scale bulk reporting.

## Explicit Block Boundaries

Phase 8 does not implement the payment engine, student profile management, course registration, result processing, staff records, or actual student conversion. The only Block 5 handoff remains the previously introduced conversion eligibility marker.

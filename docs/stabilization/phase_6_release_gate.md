# Block 1–3 production-readiness release gate

Block 4 remains blocked until every mandatory item has dated evidence and an
identified approver. Critical/high risks cannot be waived.

## Automated evidence

- [ ] The `Blocks 1-3 stabilization` CI workflow passes both the source/unit and MySQL 8.4 jobs for the release commit.
- [ ] Clean install and upgrade migrations pass on pinned MySQL 8.4; dirty-data preflights fail safely and resulting constraints are verified.
- [ ] Full unit, database/integration, HTTP feature, and browser E2E suites pass from a clean checkout.
- [ ] Dependency vulnerability scans for locked Composer and npm dependencies have no unresolved critical/high finding.
- [ ] Static secret scan and production configuration review pass.
- [ ] Concurrent reference allocation, submission, acceptance, publication, cache revision, scan claim, and outbox claim tests pass without duplicates or lost transitions.
- [ ] DB/storage/provider fault injection proves rollback or deterministic recovery.
- [ ] Representative-volume query plans, indexes, latency, memory, worker throughput, and large-export behavior meet the approved capacity baseline.

## Reference end-to-end scenario

- [ ] Platform Admin securely creates Tenant A and Tenant B, assigns domains, provisions independent admins, manages lifecycle, and uses reasoned expiring read-only support context.
- [ ] Tenant A and Tenant B independently configure institution, academics, authorities, branding, website, and admissions.
- [ ] The same normalized identifier can own separate credentials in A and B; each account has exactly one permanent membership and cannot switch tenant.
- [ ] Tenant A applicant registers, profiles, drafts/autosaves, records O'Level, uploads a document, passes scan, previews, and submits an atomic immutable snapshot/reference.
- [ ] Staff review, correction, versioned resubmission, document approval, screening, shortlist, decision/approval, offer, public-list publication, response, clearance, and conversion marker all pass.
- [ ] Required email and in-app notifications deliver; retry, dead-letter visibility, stale lock recovery, scheduler normalization, and worker crash recovery pass.
- [ ] Reports paginate/filter correctly; bounded and asynchronous exports are complete, CSV-safe, authorized, expiring, and never silently truncated.
- [ ] Audit records identify actor, tenant, target, action, and time for business and privileged platform operations.

## Negative security matrix

- [ ] Anonymous, wrong group, missing authority, suspended/revoked membership, wrong applicant, and wrong lifecycle requests fail.
- [ ] Tenant A cannot read or mutate Tenant B through hostname, IDs, payload tenant IDs, downloads, exports, media, caches, or background jobs.
- [ ] Platform identities never become tenant members and cannot silently switch or impersonate; support context expires and is visibly/auditably bounded.
- [ ] Invalid/missing CSRF fails for every mutation, including autosave after token rotation.
- [ ] IDOR, traversal, malicious upload, CSV formula, hostile filename/URL, XSS payload, login enumeration, throttling, and session-expiry tests pass.
- [ ] Repeated/idempotent and concurrent requests have deterministic outcomes.

## Product and operational acceptance

- [ ] Every Phase 5 inventory row passes desktop, mobile, keyboard, accessibility-basics, no-JavaScript fallback, link/control, console, asset, and state review.
- [ ] Shield authentication, verification, and recovery pages are production-quality in tenant context.
- [ ] Public custom-domain/subdomain behavior and reverse-proxy canonical URLs pass.
- [ ] No dead, hidden-URL-only, placeholder, future-module, or unsupported download/print/export control is exposed.
- [ ] Health probes, structured correlation logs, dashboards, metrics, alerts, runbooks, on-call ownership, retention, and incident procedure are active.
- [ ] Coordinated database/files/config backup and restore drill meets RPO ≤15 minutes and RTO ≤4 hours.
- [ ] Release and rollback rehearsal completes; worker/scheduler supervision and restart behavior are proven.
- [ ] Documentation matches deployed behavior and lists only explicitly accepted lower-severity limitations.

## Current repository evidence status (2026-09-26)

Source lint and frontend build can execute in this checkout. Composer dependencies,
MySQL 8.4, provider/scanner/object-store infrastructure, browser runtime evidence,
load data, and backup systems are unavailable here. Therefore this repository
cannot yet be signed off as production ready, regardless of source completion.

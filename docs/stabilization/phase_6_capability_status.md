# Blocks 1–3 capability and evidence status

Status is deliberately evidence based. `Implemented` means a supported code path
exists; it does not mean production verification has passed.

| Capability | Implemented | Required production evidence still outstanding in this checkout |
|---|---:|---|
| Singular tenant identities and Shield broad groups | yes | Shield session/recovery E2E and dirty-data migration rehearsal |
| Tenant resolution and cross-tenant rejection | yes | two-host, two-tenant HTTP/IDOR matrix |
| Platform tenant lifecycle and support context | yes | privileged audit/session-expiry browser scenario |
| Institution/academic configuration and authorities | yes | complete admin workflow and concurrency/constraint checks on MySQL 8.4 |
| Public website/CMS/editorial/media | yes | custom-domain/proxy, scheduled publishing, cache, media, mobile/browser scenario |
| Applicant profile/draft/autosave/O'Level/documents | yes | Shield/CSRF/browser flow, scanner, private object store, ownership matrix |
| Immutable submission/reference | yes | concurrent/repeated submission and rollback fault injection on MySQL 8.4 |
| Review/correction/screening/shortlisting/decision | yes | two-tenant E2E with immutable resubmission traceability |
| Offer/publication/acceptance/clearance handoff | yes | repeated/concurrent action and public-list browser scenario |
| Email and in-app notifications | yes | SES sandbox, worker crash/recovery, bounce and dead-letter operations |
| Reports and CSV safety | partial | interactive pagination and approved asynchronous large-export artifact flow |
| Malware scanning and storage abstraction | partial | ClamAV and production S3-compatible adapter/recovery evidence |
| UI/product surface | implemented | full Phase 5 desktop/mobile/keyboard/accessibility/console evidence |
| Observability/recovery/release controls | implemented contract | deployed dashboards/alerts and coordinated restore/release rehearsal |

## Known bounded limitations

- Payments, Student Records, Staff Records, Course Allocation, Course Registration,
  and Results are not Block 1–3 capabilities and must remain unexposed.
- SMS is not a required channel until a provider is approved.
- Report export is not production complete until the asynchronous large-export
  flow is delivered and volume tested.
- Local document storage and the ClamAV process adapter are development/initial
  adapters; production object storage and scanner infrastructure need acceptance.

## Block 4 entry prerequisites

Every item in `phase_6_release_gate.md` must pass. In particular, there may be no
open critical/high security risk, tenant isolation must be proven on real HTTP and
MySQL paths, backup/restore must meet RPO/RTO, workers and alerts must be active,
and the Phase 5 product inventory must have browser evidence. A roadmap or source
implementation alone cannot authorize Block 4.

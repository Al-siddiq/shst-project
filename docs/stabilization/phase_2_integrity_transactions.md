# Phase 2 — Data Integrity, Transactions, and Trusted Foundation Services

## Production database contract

Production is MySQL 8.4 LTS, InnoDB, utf8mb4, with `READ COMMITTED` as the
default transaction isolation. Locking-sensitive operations use an explicit
row lock or atomic conditional update; application validation alone is not a
concurrency control.

## Business transaction convention

The application service owns the transaction. Controllers validate transport
input and format responses; models persist tenant-scoped records. A critical
mutation, mandatory audit row, and required outbox intent commit together. A
failure in any one fails the whole mutation. Provider/network calls never run
inside the database transaction. Nested service calls participate in the same
CodeIgniter connection transaction.

`TransactionalService::run()` is the common boundary. It enables transaction
exceptions, rolls back exceptions, completes the transaction, and rejects a
failed transaction status. `AuditLogger::record()` and the admissions outbox
dispatcher throw when their mandatory insert does not succeed.

Operational telemetry (failed-login counters, metrics, secondary logs) is not
transactional business audit and may follow an availability-preserving policy.

## Tenant relational integrity

The migration preflight rejects duplicate applications and existing mismatches
across documents, profiles, applications, cycles, programme openings, and
programmes. MySQL receives composite tenant-aware foreign keys for the critical
application/opening/document chain. Services and `TenantScopedModel` remain the
first boundary; database constraints are defense in depth.

Additional composite keys should only be added when a relationship is both
tenant-security-sensitive and stable enough to justify migration locking. They
must always be preceded by dirty-data queries and representative-volume timing.

## Idempotency and concurrency

- Admission reference counters are initialized idempotently and locked before
  allocation; the conditional increment must affect exactly one row.
- `TenantRowLock` performs allow-listed `(tenant_id, id)` row locks for admission
  lifecycle transitions so a hostname or unscoped ID cannot select the lock.
- Final submission locks the application row, checks the lifecycle again, uses
  optimistic `lock_version`, and returns the existing response on a repeat.
- Submission snapshots are immutable versions rather than overwritten rows.
- Outbox intents have tenant/channel idempotency keys and claim/retry metadata.
- `idempotency_records` is reserved for externally repeated commands whose
  resource response must be replayed; it is not a substitute for natural keys.
  `IdempotencyService` locks a tenant/operation/key record, verifies a canonical
  request hash, and replays only the committed response. Reusing a key with
  different input is an error.
- Public website revision counters are persisted in `tenant_cache_revisions`
  and advanced with an atomic database update rather than a racy cache
  read-modify-write sequence.

## File/database consistency and quarantine

Applicant uploads are written to a private quarantine namespace first. Metadata,
application timestamp, and mandatory audit commit atomically. If the database
operation fails, the staged blob is removed. A replaced blob is removed only
after the new metadata commits. New files are `quarantined/pending`; legacy files
are `legacy_unverified/not_scanned`. Both fail closed for download/submission.

Phase 4 supplies the replaceable malware scanner, promotion into active private
storage, retries, and reconciliation worker. This is an intentional dependency,
not permission to expose pending files.

## Lifecycle vocabulary established here

- Document storage: `quarantined`, `active`, `rejected`, `missing`, `legacy_unverified`.
- Scan: `pending`, `clean`, `infected`, `scanner_unavailable`, `not_scanned`.
- Outbox: `pending`, `processing`, `sent`, `retry`, `dead`.
- Idempotency: `processing`, `completed`, `failed`.

Transitions must be service-owned, tenant-scoped, audited where business
significant, and concurrency protected. Phase 4 implements workers and delivery.

Soft-deleted tenant business keys are not silently reusable. Reuse requires an
explicit lifecycle rule and migration; otherwise the database unique key retains
historical ownership. Status strings above are the canonical vocabulary until a
later migration deliberately replaces them with reference tables or constraints.

## Migration operation

Run all preflight queries in
`docs/stabilization/sql/phase_2_trusted_mutation_foundation.sql` before the
migration. Returned rows require explicit tenant-correct data remediation. Take
a recoverable database and file snapshot before DDL. Rollback loses scan,
idempotency, and outbox claim metadata and therefore requires approval.

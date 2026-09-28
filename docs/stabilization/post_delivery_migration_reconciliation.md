# Post-delivery migration reconciliation

## Root cause

`2026-05-28-140000_CreateTenantAccessControlTables` owns the addition of
`tenant_memberships.status` and `membership_label`. The later
`2026-06-02-100000_CreateAdmissionReadinessTables` attempted to add the same two
columns again. Both responsibilities were introduced in their original commits;
this was not caused by Phase 1–6 changing an already-applied migration. On a fresh
or pre-admissions database, CodeIgniter correctly ran the May migration first and
MySQL then rejected the June migration with `Duplicate column name 'status'`.

The canonical evolution is now unambiguous: the May access-control migration owns
membership lifecycle columns; the June admissions migration owns applicant
profiles, documents, and reference sequences only. Its rollback must therefore
not remove membership columns owned by the May migration.

## History and schema reconciliation

CodeIgniter records application migrations in the configured `migrations` table
using namespace, version, class, batch, and execution time. Application migrations
use namespace `App`, the `Y-m-d-His_` filename format, and migration locking is
enabled. `php spark migrate` targets the default application namespace; deployment
uses `php spark migrate --all` so Shield/package and application namespaces are
also advanced. These commands are not interchangeable when package migrations
remain pending.

Before recovery, export these read-only diagnostics:

```sql
SELECT id, version, class, `group`, namespace, batch, time
FROM migrations ORDER BY id;
SHOW CREATE TABLE tenant_memberships;
SHOW CREATE TABLE applicant_profiles;
SHOW CREATE TABLE application_documents;
```

- If the June readiness migration is absent and no admissions readiness tables
  exist, deploy the corrected file and rerun `php spark migrate --all`.
- If it is absent after the reported duplicate-column failure, no June DDL ran:
  the duplicate statement was first. Deploy the correction and rerun.
- If it is absent but one or more June-owned tables exist, the migration stopped
  later. Do **not** insert a history row. Back up the database, compare each table
  with the migration, remove only empty artifacts created by that failed attempt,
  and rerun. If an artifact contains valid data, stop for an approved data-preserving
  forward reconciliation migration rather than dropping it.
- If the history row exists, never rerun or edit history manually. Validate the
  terminal schema and apply only a new forward reconciliation migration if drift
  exists.
- A fully migrated database must report `Nothing to migrate` on a second
  `php spark migrate --all`; its migration history must contain one row per
  namespace/version/class.

MySQL DDL auto-commits, so application transactions cannot make a multi-statement
migration atomic. Recovery therefore requires a backup, captured history/schema,
and stage-aware remediation—not blanket `hasColumn()` guards that could bless an
unknown partial schema.

## Chain audit findings

1. Membership lifecycle columns had duplicate ownership; corrected as above.
2. The June rollback incorrectly removed May-owned columns; corrected.
3. Migration locking was disabled; enabled to prevent concurrent migrators.
4. Stabilization raw SQL reversed `ON DELETE` and `ON UPDATE` actions for Phase 1
   history/support FKs and Phase 2/4 tenant FKs. SQL now matches PHP intent:
   `ON DELETE RESTRICT ON UPDATE CASCADE`.
5. The Phase 2 MySQL migration intentionally creates supporting composite unique
   indexes before composite foreign keys. Its preflight rejects duplicate logical
   applications and cross-tenant relationships before DDL.
6. Stabilization migrations contain non-transactional multi-statement MySQL DDL;
   the stage-aware recovery procedure above is mandatory after interruption.
7. `CreateApplicantSubmissionTables` was changed after its original commit only
   to apply the configured table prefix to an index statement. This does not alter
   an unprefixed production schema, but installations using prefixes must use the
   corrected canonical file.
8. Base migrations used `CREATE TABLE IF NOT EXISTS`. That can silently accept a
   table left incomplete by an interrupted, unrecorded migration. Canonical
   migrations now create tables without the permissive flag so unknown partial
   state fails visibly and follows the recovery procedure instead of being blessed.

## Raw SQL parity

The stabilization SQL files were compared to the PHP migrations for column type,
nullability, defaults, unique/index definitions, FK columns, and referential
actions. The action-order discrepancies above were corrected. SQL remains MySQL
8.4-specific; SQLite test branches intentionally do not represent production FK
behavior.

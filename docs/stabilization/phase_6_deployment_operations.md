# Phase 6 — Deployment and operations runbook

This is the production contract for Blocks 1–3. Environment bootstrapping is not
part of stabilization implementation, but a release must satisfy every gate below.

## Supported runtime

- PHP 8.2+ with `intl`, `mbstring`, `json`, `openssl`, `mysqli`, and required image/file extensions.
- Composer dependencies installed from `composer.lock` with optimized production autoloading.
- Node assets built from a committed lock file using `npm ci && npm run build`.
- MySQL **8.4 LTS / InnoDB / utf8mb4**, with the supported maintenance release pinned by operations.
- Default transaction isolation **READ COMMITTED**. Stronger guarantees use the explicit locks in application services.
- Web root is `public/`; `writable/`, source, environment files, and private objects are never web-served.
- HTTPS is mandatory. Configure `app.forceGlobalSecureRequests=true`, exact `app.allowedHostnames`, and only trusted reverse-proxy CIDRs in `app.proxyIPs`.
- Production uses `CI_ENVIRONMENT=production`, `CI_DEBUG=false`, secure/HTTP-only cookies, and secrets from the deployment secret store.

`php spark release:preflight` is a read-only gate for PHP/extensions, writable
paths, database connectivity, MySQL version/isolation, and critical tables. Any
failure blocks traffic promotion.

## Release procedure

1. Record application commit, dependency lock hashes, MySQL version, and asset-build hash.
2. Confirm current database **and object/file** backups are restorable and within the 15-minute RPO.
3. Drain workers, take the coordinated pre-migration recovery point, and run migration preflight SQL under `docs/stabilization/sql/`.
4. Apply migrations once with `php spark migrate --all`; never run concurrent migrators.
5. Run `php spark release:preflight`, migration-status checks, and the smoke/security suite.
6. Build assets, deploy immutable application files, warm no tenant-sensitive shared cache keys, then restart PHP processes.
7. Start `admissions:work` workers and schedule `application:maintain` once per minute with overlap protection.
8. Check `/health/live`, `/health/ready`, login, one tenant public site, worker lag, scanner, sandbox email, and logs before promoting traffic.
9. Observe errors, latency, DB saturation, outbox lag/dead rows, scan backlog, disk/object capacity, and queue recovery throughout the release window.

Schema rollback is not assumed safe after writes begin. Prefer forward remediation.
If migration validation fails before traffic promotion, stop the release and restore
the coordinated recovery point. Application rollback is permitted only when its
schema compatibility has been explicitly proven.

## Workers and schedules

- Run one or more `php spark admissions:work 50` processes under a supervisor. Restart on non-zero exit and alert on crash loops.
- Run `php spark application:maintain` every minute with a scheduler-level lock.
- Alert if pending/retry notification age exceeds five minutes, dead notifications are non-zero, scan backlog age exceeds five minutes, or the scheduler has not succeeded for three minutes.
- Worker claims older than fifteen minutes are recoverable; test worker termination after claim and before completion during release rehearsal.

## Security configuration

- Permit only intended hosts at the edge and in CodeIgniter configuration; reject direct origin access.
- Trust forwarded headers only from enumerated proxy ranges.
- Apply HSTS at the edge after HTTPS validation. Global secure headers are enabled in the application.
- Keep database, SES, object-store, scanner, and encryption credentials outside repository/business tables/logs; rotate through the secret manager.
- Restrict object-store policies by logical area. Applicant and quarantine objects require application-authorized access and must never receive public ACLs.
- Restrict health endpoints at the load-balancer/network where practical. They return component state only and never secrets or exception details.

## Backup and restore

Engineering targets are RPO ≤15 minutes and RTO ≤4 hours. Back up and recover
the database, public media, private applicant objects, quarantine where policy
requires it, generated artifacts, and required encryption/configuration material
as one versioned system. Daily backups target 35 days; monthly backups target 12
months. Legal/business owners must approve applicant-document and audit retention.

Quarterly restore drill:

1. Restore to an isolated account/network with outbound email disabled.
2. Restore database and object/file snapshots from the same recovery point.
3. Restore necessary keys/configuration without copying live disposable credentials.
4. Run preflight, checksums/object sampling, tenant-isolation tests, applicant document authorization tests, and a notification dry run.
5. Measure achieved RPO/RTO, document discrepancies, securely destroy the drill environment, and track remediation.

## Observability and incident response

Every HTTP response receives `X-Request-ID`; completion logs contain request ID,
method, redacted path shape, and status but no body, credential, identifier,
document token, or document content.
Centralize production logs for about 90 days with access control and redaction.

Monitor at minimum: request rates/latency/5xx/403/429, database connections and
slow queries, cache errors, filesystem/object capacity, outbox status/age, scanner
status/backlog, scheduler heartbeat, email failures/bounces, and backup age. Page
on sustained 5xx, readiness failure, cross-tenant/security anomaly, backup target
breach, dead notifications, scanner outage, or capacity exhaustion.

For a suspected tenant-boundary or credential incident: preserve evidence, revoke
affected sessions/credentials, stop unsafe workers or routes, identify impacted
tenant IDs from audit/request IDs, notify the incident owner, contain before
recovery, and record a blameless timeline. Never delete audit evidence during
containment. Legal notification timelines require organizational approval.

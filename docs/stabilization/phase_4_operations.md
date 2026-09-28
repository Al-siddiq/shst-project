# Phase 4 — Blocks 2–3 Functional and Operational Completion

## Worker and scheduler contract

Run `php spark admissions:work 50` continuously or at least once per minute. It
claims notification and document-scan rows with `FOR UPDATE SKIP LOCKED`; claims
older than fifteen minutes are recoverable after worker failure. Run
`php spark application:maintain` each minute to normalize due website content and
expired offers. Both commands are idempotent and safe to overlap.

Notification failures use exponential backoff and become `dead` after the
configured maximum attempts. Operators see pending, processing, retry, sent, and
dead counts and may explicitly retry non-sent rows. Provider operations occur
outside database transactions. Only provider message IDs and bounded failure
details are stored; credentials remain environment configuration.

## Email and in-app delivery

Admissions events enqueue both required channels when the applicant has an email.
The email provider is behind `EmailProviderInterface`; the initial adapter uses
CodeIgniter Email and is compatible with Amazon SES SMTP configuration. Production
must configure verified sender/domain, credentials outside the database, bounce
handling, and sandbox-to-production promotion. In-app notifications are durable,
tenant scoped, and shown in the applicant dashboard.

## Document scanning and storage

`PrivateDocumentStorageInterface` separates the domain from the local adapter;
production may bind an S3-compatible adapter. The current adapter maintains
separate `quarantine/` and `private/` namespaces. `MalwareScannerInterface` is
replaceable; the initial ClamAV adapter invokes `clamdscan` with a bounded timeout.
Scanner unavailability fails closed and retries. Infected objects remain rejected
in quarantine. Clean objects are promoted, and a database failure compensates by
moving them back. Review, download, and submission require `active/clean`.

## Scheduled publication

Anonymous reads already treat due scheduled records as published, so correctness
does not depend on cron timing. The maintenance command subsequently normalizes
status, audit, and cache generation. This is the approved read-time-correct plus
idempotent-normalization model.

## Admissions corrections and operational vocabulary

Correction requests enqueue applicant email/in-app messages. Resubmission creates
a new immutable snapshot version and preserves the original. Decision reports use
the canonical `offered`, `waitlisted`, and `rejected` vocabulary. Expired offers
become `offer_expired`. CSV exports no longer silently stop at 1,000 rows and
neutralize spreadsheet formula prefixes.

## Remaining bounded production work

The current synchronous CSV implementation is safe from silent truncation but
does not yet implement the approved asynchronous large-export artifact workflow.
Production object storage, SES infrastructure compatibility, ClamAV availability,
and representative-volume worker sizing require environment verification. These
are release blockers, not claims of completion.

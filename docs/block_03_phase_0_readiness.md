# Block 3 Phase 0 — Identity, Security, and Admission Readiness

## Objective

Phase 0 closes the security prerequisites for later admissions work without implementing admission cycles or application forms. It establishes Shield as the global identity foundation, tenant-resolved applicant entry, Nigerian phone normalization, tenant-owned applicant profiles, private applicant-document metadata and authorized delivery, operational authorities, CSRF posture, and a tenant-aware reference generator skeleton.

## Shield Installation and Migration

The repository declares `codeigniter4/shield` in `composer.json`. A network-enabled deployment environment must run:

```bash
composer update codeigniter4/shield --with-dependencies
php spark migrate --all
```

The application-owned `Config\\Auth`, `Config\\AuthGroups`, and `Config\\AuthToken` classes extend Shield defaults. Broad Shield identity groups remain separate from tenant operational authorities. Admission-officer responsibilities must not be modeled as Shield groups.

## Tenant-Aware Applicant Entry

Applicants begin at `/apply` or `/t/{tenantSlug}/apply`. The existing tenant-context and public-tenant filters resolve the institution and reject inactive tenants before rendering the entry page. Later registration and draft work must preserve this resolved context. No hidden browser field is trusted as the tenant source.

## Authentication and Identifier Policy

- Shield owns registration, login, logout, verification, recovery, session regeneration, and authentication throttling.
- `ApplicantIdentityNormalizer` lowercases valid email identifiers.
- Nigerian mobile inputs such as `080...`, `23480...`, and `+23480...` normalize to one E.164 value.
- Applicant authorization requires Shield authentication, resolved tenant context, and an active applicant profile owned by the authenticated user.
- Tenant staff document review requires active membership and the `admissions.documents.review` operational authority.

## CSRF Strategy

CI4 CSRF remains enabled explicitly on browser mutation route groups. CSRF tokens are randomized in `Config\\Security`. The Phase 0 applicant-document route is a read-only authorized download route. Later applicant uploads and form mutations must add the `csrf` filter.

## Private Applicant Documents

`application_documents` stores metadata only. Physical files belong beneath `WRITEPATH/uploads/admissions` using server-generated relative paths. Applicant documents are not website media, do not have public URLs, and must be streamed through an authorized controller. Phase 3 adds upload persistence and replacement behavior on top of this fail-closed boundary.

## Phase Boundary

Phase 0 intentionally does not implement:

- admission-cycle records;
- programme openings;
- requirement builders;
- applicant form steps;
- document uploads;
- application submission;
- application review;
- payment behavior;
- student conversion.

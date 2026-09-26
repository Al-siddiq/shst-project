# Phase 1 — Identity, Tenant Boundary, and Security Hardening

## Status

Implementation delivery record for the first stabilization phase. Tenant-scoped
Shield identifier lookup remains blocked pending verification of the supported
Shield 1.3.0 provider/authenticator extension point; see **Known limitation**.

## Authoritative identity rules

1. A normal Shield user is permanently bound to exactly one tenant.
2. Applicants receive tenant membership. Membership grants tenant association,
   not staff or administrative authority.
3. Shield is the sole runtime source of broad identity groups.
4. Each tenant identity has one primary broad class.
5. Operational responsibilities remain tenant operational authorities.
6. Platform administrators have no tenant membership.
7. Platform tenant access is explicit, short-lived, read-only by default,
   visibly indicated, reasoned, and audited.
8. A requested tenant must match the authenticated user's membership.
9. State-changing browser requests require CSRF protection.

## Request authorization chain

`authentication → singular membership → tenant match → Shield group → operational authority → resource/lifecycle policy`

Hostnames, route slugs, browser-supplied tenant IDs, and hidden UI controls are
never authorization evidence.

## Route classes

| Class | Required boundary |
|---|---|
| Anonymous public tenant | Tenant resolution plus public tenant eligibility |
| Authentication/recovery | CSRF, tenant context resolution, sensitive rate limit |
| Applicant | Shield auth, resolved matching membership, applicant group, owned profile/resource |
| Tenant staff | Shield auth, matching active membership, approved Shield group, operational authority |
| Platform | Shield auth, platform_admin group, no tenant membership |
| Platform support | Platform boundary plus explicit active support context; read-only |

## Membership migration preflight

Before applying `2026-09-22-100000_HardenIdentityTenantBoundary` run the query in
`docs/stabilization/sql/phase_1_identity_tenant_boundary.sql`. Every returned user
must be remediated by retaining one account/tenant binding and provisioning new
Shield accounts for other tenants. The migration deliberately fails rather than
guessing which tenant should own an ambiguous account.

Bindings use lifecycle status (`active`, `suspended`, `revoked`) and are not
reusable soft-deleted slots. Rollback of the uniqueness rule requires explicit
architectural approval.

## Legacy broad-group migration

`tenant_iam_group_assignments` is no longer a runtime authorization source and
new writes are rejected. Existing records must be migrated to Shield groups only
after the exact Shield migration/provider API is available and verified. The
legacy table is retained temporarily for rollback evidence and must not be read
for authorization.

## Known limitation: Shield tenant-scoped identifiers

The repository locks CodeIgniter Shield 1.3.0, but the execution environment was
unable to download the package source from GitHub (HTTP 403), leaving `vendor/`
incomplete. The approved tenant-scoped identifier design requires verification
against the actual Shield provider/model/authenticator extension contract and
the uniqueness constraints of Shield's identity table.

No vendor patch, synthetic hidden identifier, or parallel password system has
been introduced. Until a supported extension is verified and implemented:

- duplicate human email/phone identifiers across tenants are **not declared supported**;
- Phase 1 tenant-scoped authentication acceptance criteria remain incomplete;
- production release is blocked;
- the recommended implementation remains a tenant-aware Shield provider/model
  that resolves `(tenant, normalized identifier)` while Shield continues to own
  passwords, sessions, verification, recovery, and throttling.

## Security response rules

- Anonymous protected HTML requests redirect to `/auth/login`.
- JSON/AJAX requests receive a standard `401` response.
- Authenticated but unauthorized requests receive `403`.
- Cross-tenant denials do not disclose whether records exist in another tenant.
- Recovery and registration responses must not enumerate identifiers across tenants.
- Sensitive endpoints use per-IP throttling in addition to Shield protections.


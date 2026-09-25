# Phase 3 — Block 1 Operational and Administrative Completion

## Capability checklist

| Capability | Normal UI | Authorization | Audit | Status |
|---|---|---|---|---|
| Platform tenant list/create/profile | `/platform/tenants` | platform admin only | mandatory | implemented, runtime verification pending |
| Tenant lifecycle | tenant detail | platform admin only; reason required | mandatory | implemented, runtime verification pending |
| Domain registry | tenant detail | platform admin only | mandatory | implemented, runtime verification pending |
| Controlled platform support | `/platform/support` | platform admin; no membership | mandatory | implemented in Phase 1 |
| Tenant administrator credential provisioning | — | tenant-aware Shield provider required | required | **blocked; see below** |
| Tenant dashboard/navigation | `/tenant/admin` | matching membership, Shield group, authority | read-only | implemented, runtime verification pending |
| Institution and academic structure | `/tenant/admin/configuration` | school configuration authority | mandatory | implemented, runtime verification pending |
| Membership lifecycle | `/tenant/admin/access` | tenant super admin | mandatory + history | implemented, runtime verification pending |
| Shield primary broad-group assignment | — | Shield provisioning boundary | required | **blocked; see below** |
| Operational authorities/grants | `/tenant/admin/access` | tenant super admin | mandatory | implemented, runtime verification pending |
| School/department branding | `/tenant/admin/branding` | school configuration authority | mandatory | implemented, runtime verification pending |
| Domain visibility | `/tenant/admin/domains` | school configuration authority | read-only | implemented, runtime verification pending |
| Tenant audit | `/tenant/admin/audit` | tenant super admin | n/a | implemented, runtime verification pending |
| Website/CMS and Admissions navigation | authorized tenant navigation | group + authority | n/a | exposed only when permitted |

## Known blocking boundary: Shield account provisioning

The repository locks Shield 1.3.0, but its source is unavailable because Composer
downloads are denied by the current environment. The approved identity contract
requires tenant-scoped duplicate human identifiers and tenant-context recovery.
It is unsafe to implement administrator credential provisioning until the exact
Shield provider/authenticator/user APIs and identity constraints can be verified.

Consequently this phase deliberately does **not** expose a form asking for raw
user IDs, write the retired tenant IAM table, synthesize hidden identifiers, or
create a parallel password store. Membership lifecycle is operable for already
provisioned accounts; new tenant-admin credential provisioning and primary group
assignment remain a Phase 3 blocker rather than a falsely completed feature.

## Supported user paths

- Platform administrators reach school management and controlled support through
  the platform navigation without hidden URLs.
- Tenant super administrators reach configuration, access, branding, domains,
  website/CMS, admissions, and audit according to their authorities.
- Tenant administrators see configuration/product modules they are authorized to
  use; they cannot reach tenant-super-admin access or audit functions.
- Lecturer and student future-module interfaces are not exposed by Block 1.

## Phase exit verification still required

Execute the HTTP feature tests through real Shield sessions, CSRF, routing, and
tenant filters; execute database tests on MySQL 8.4; manually inspect desktop and
mobile forms, links, lifecycle confirmations, tenant branding, and authorization
denials. Phase 3 cannot be production-approved while tenant-aware Shield account
provisioning remains blocked.

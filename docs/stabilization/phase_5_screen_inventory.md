# Phase 5 — Screen and action inventory

This is the acceptance inventory for every production-visible Block 1–3 surface.
Each row must remain reachable through normal authorized navigation, have an
intentional layout, and expose no action that lacks a working backend route.

| Audience | Surface | Normal entry point | Layout/state contract | Phase 5 disposition |
|---|---|---|---|---|
| Platform admin | Tenant list/create | Platform navigation → Schools | responsive table/form, empty, validation, success/error | supported |
| Platform admin | Tenant detail/lifecycle/domains/admin provisioning | Schools → Manage | destructive confirmation, lifecycle badge, validation | supported |
| Platform admin | Controlled support access | Platform navigation → Support access | prominent support warning, reason, expiry, end action | supported |
| Tenant admin | Dashboard | School navigation → Dashboard | metrics and capability cards | supported |
| Tenant admin | Institution and academic configuration | School navigation → School Configuration | labelled forms, record lists, validation/messages | supported |
| Tenant super admin | Memberships and authorities | School navigation → Access Control | permission gated, lifecycle controls, explicit authority grants | supported |
| Tenant admin | Branding and department identity | School navigation → Branding | token-driven colors and intentional forms | supported |
| Tenant admin | Domains | School navigation → Domains | domain records and empty state | supported |
| Tenant super admin | Audit | School navigation → Audit | readable event records | supported |
| Website editor | Website dashboard | School navigation → Website & CMS | readiness, metrics, work queue, empty states | supported |
| Website editor | Settings, menus, editorial, media, showcase, audit | Website dashboard/workspace links | server fallback, Vue progressive enhancement, status feedback | supported |
| Public visitor | Home/about/contact/academics/management/editorial/gallery | Tenant public navigation | branded responsive shell, empty/not-found states | supported |
| Public visitor | Admissions/programmes/lists/apply | Tenant public navigation → Admissions | only published data and live actions | supported |
| Applicant | Profile/dashboard/application/O'Level/documents/preview | tenant-scoped authentication → applicant dashboard | autosave status, validation, scan state, empty/success/error | supported |
| Applicant | Offer acceptance/decline | Dashboard/notification → Offers | explicit choice, CSRF, no raw IDs | supported |
| Admissions staff | Dashboard/setup/review/decisions/lists/acceptance/reports/operations | School navigation → Admissions | authority-gated actions, metrics, tables/mobile overflow, empty/error states | supported |
| Authentication | Login/registration/verification/recovery | tenant auth routes | Shield-owned forms; tenant context and production theme required | supported via Shield; final runtime review required |
| Errors | 400/403/404/exception/session expiry | router/framework | safe production copy, no stack trace, recovery link | framework-owned; final runtime review required |
| Lecturer/student future modules | any future dashboard or navigation | none | must not be linked or advertised | hidden/out of scope |

## Visible-capability audit

1. Extract every GET route and map it to its controller/view and authorization filters.
2. Extract links, forms, buttons, `data-*` enhanced actions, download/print/export
   labels, and verify each target against the route inventory.
3. Exercise each surface as Platform Admin, Tenant Admin, Website Editor,
   Admissions Staff, Applicant, anonymous visitor, wrong-tenant user, and user
   missing the relevant authority.
4. Record desktop/mobile screenshots and keyboard traversal in the release evidence.
5. A control is retained only when its complete normal workflow passes. Otherwise
   remove it from production navigation and document the bounded capability.

The permanent rule is: **if a feature is visible, it must work; if it does not
work, it must not be exposed.** Source-string checks support this inventory but
never replace HTTP, browser, accessibility, or end-to-end verification.

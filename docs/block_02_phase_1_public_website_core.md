# Block 2 Phase 1 Delivery Note
## Public Website Core

Phase 1 establishes the tenant-branded, database-driven public website shell. It builds directly on the Phase 0 tenant guard and media foundation and intentionally stops before department/programme showcase extensions, admission-information records, editorial publishing, galleries, management profiles, and Vue CMS islands.

## Delivered Public Core

### Tenant website settings

`website_settings` stores one tenant-owned presentation record. It includes launch state, public identity copy, homepage hero copy and optional media, about-page content, public contact overrides, portal/application-information placeholders, metadata, and social links. When an optional public contact override is absent, the service falls back to Block 1 tenant profile data rather than hard-coded school values.

### Tenant public navigation

`website_menu_items` stores ordered public links per tenant. The public menu service loads visible active items only, resolves safe internal and external links, and builds nested menu records without database calls from templates.

### Public layout and pages

The server-rendered public layout provides tenant theme CSS variables, responsive mobile navigation, semantic landmarks, skip navigation, metadata, canonical URLs, footer contact details, and optional portal/application-information links. Homepage, about, and contact pages render tenant-scoped records and neutral empty states.

### Tenant-aware URLs and caching

The public URL generator preserves `/t/{slug}` paths when route-slug resolution is used and prefers an active primary tenant domain for canonical links. The cache wrapper requires a resolved tenant and prefixes all public-site cache keys with its tenant ID to prevent shared-cache leakage.

### Public launch guard

The Phase 0 public tenant guard now requires both an active tenant and `website_settings.is_public_enabled = 1`. Missing settings, unresolved tenants, and disabled websites receive the existing neutral unavailable response.

## Public Routes

| Method | Route | Purpose |
| --- | --- | --- |
| `GET` | `/` | Public homepage on a tenant hostname |
| `GET` | `/about` | Public about page on a tenant hostname |
| `GET` | `/contact` | Public contact page on a tenant hostname |
| `GET` | `/t/{tenantSlug}` | Route-slug fallback homepage |
| `GET` | `/t/{tenantSlug}/about` | Route-slug fallback about page |
| `GET` | `/t/{tenantSlug}/contact` | Route-slug fallback contact page |
| `GET` | `/t/{tenantSlug}/media/{id}/{variant}` | Route-slug fallback public derivative |

## Scope Boundary

Phase 1 stops after the public shell and settings source. Public department and programme extensions, admission-information records, editorial lifecycle content, gallery records, public management profiles, CMS management pages, and Vue enhancements belong to later approved phases.

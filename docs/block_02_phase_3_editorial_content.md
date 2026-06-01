# Block 2 Phase 3 Delivery Note
## Editorial Content and Lifecycle

Phase 3 adds tenant-scoped news, public announcements, and public calendar notices. It builds on the Phase 0 media foundation and Phase 1 public shell while preserving explicit editorial lifecycle commands and tenant-aware caching.

## Delivered Editorial Foundation

### Shared editorial records

`website_content_items` stores `news`, `announcement`, and `calendar_notice` records with tenant ownership, slugs, summaries, bodies, optional public media, related tenant academic records, audience, event dates, metadata, scheduling timestamps, publication attribution, timestamps, and soft deletion.

### Public lifecycle

The supported internal states are:

- `draft`
- `scheduled`
- `published`
- `archived`

Anonymous public queries expose published records and scheduled records whose `scheduled_for` time has arrived. Drafts, archived records, future scheduled records, non-public announcements, announcements outside their display window, and private calendar notices remain hidden.

### Explicit tenant-admin commands

Tenant-admin routes separate draft editing from lifecycle transitions:

- editors with `website.content.create` may create drafts;
- editors with `website.content.edit` may revise drafts and move eligible records back to draft;
- publishers with `website.content.publish` may schedule and publish;
- users with `website.content.archive` may archive;
- users with `website.content.delete` may soft-delete drafts.

Each lifecycle command is audited. Cache invalidation bumps a tenant-scoped editorial revision so homepage, detail, and every paginated listing cache key become unreachable after changes without requiring cache-handler-specific tags. Cache lifetimes also stop at the nearest scheduling or announcement-window boundary so due content appears without a cron dependency.

### Public pages and homepage aggregation

Public routes provide news, announcements, and calendar-notice listing/detail pages for hostname and `/t/{tenantSlug}` deployments. Listings paginate. The homepage adds up to three visible records from each populated editorial section and omits empty sections cleanly.

## Scope Boundary

Phase 3 stops after editorial content and lifecycle delivery. It does not add management profiles, gallery albums, gallery items, a media-library UI, website dashboard metrics, menu-management UI, Vue CMS islands, applications, payments, students, staff, course allocation, course registration, or results.

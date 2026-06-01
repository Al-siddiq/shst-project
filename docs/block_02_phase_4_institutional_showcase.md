# Block 2 Phase 4 Delivery Note
## Management Profiles, Gallery, and Media Library

Phase 4 completes the institutional showcase with tenant-owned public imagery. Standalone leadership profiles remain intentionally separate from later staff records. Gallery albums and ordered items reuse Phase 0 media metadata rather than copying file paths.

## Delivered Scope

- `management_profiles`, `gallery_albums`, and `gallery_items` tenant-scoped tables;
- public management and paginated gallery pages with responsive derivatives;
- ordered gallery item controls and audited add, remove, reorder, create, update, and archive actions;
- progressively enhanced media library with safe reusable media IDs and upload progress;
- public-media enforcement whenever a published profile, album, or item is linked;
- tenant-scoped cache revision invalidation after institutional showcase mutations.

The media picker remains server-rendered with a small progressive-enhancement script because this repository does not yet provide a Vue compilation pipeline. This preserves the approved CI4 architecture and avoids introducing an unrelated frontend build-system refactor.

## Scope Boundary

Phase 4 stops after management profiles, gallery albums/items, and the media library. It does not add the Phase 5 website dashboard, menu manager, audit-history UI, Vue dashboard islands, applications, payments, student records, staff records, course allocation, course registration, or results.

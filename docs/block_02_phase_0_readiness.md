# Block 2 Phase 0 Delivery Note
## Block 1 Readiness and Hardening

Phase 0 prepares the existing Block 1 foundation for the public website module. It intentionally does not implement Phase 1 website settings, menus, public layout, homepage, about page, or contact page.

## Delivered Readiness Foundations

### Tenant-owned media catalogue

The `media_files` table records tenant-scoped metadata for website images. Physical files remain under writable storage rather than directly executable public paths. The server generates storage keys, validates inspected image MIME type, extension, byte size, and dimensions, and generates responsive derivatives before a media record is accepted.

Supported visibility levels are:

- `public`
- `private`

Supported initial website categories are:

- `tenant_logo`
- `homepage_hero`
- `content_featured_image`
- `department_image`
- `programme_image`
- `management_photo`
- `gallery_image`

### Public tenant eligibility guard

`PublicTenantGuard` separates tenant resolution from anonymous publication eligibility. A resolved tenant may remain available to controlled internal setup flows while anonymous media and future public website pages are denied unless the tenant status is `active`.

### Trusted tenant mutation attribution

`TenantScopedModel` now applies tenant ownership and trusted actor attribution at the model layer. Authenticated inserts receive `created_by` and `updated_by`; authenticated updates receive `updated_by`. Client-submitted actor values are overwritten when a trusted identity exists.

### Website operational authorities

The tenant authority catalogue now includes granular capabilities for website dashboard access, settings, editorial lifecycle actions, menus, media, and audit review. Assigning the `tenant_super_admin` group provisions these website authorities through an idempotent service. The demo seeder also provisions and grants them to each demo tenant super administrator so local development can exercise Phase 0 endpoints.

## Phase 0 Media Endpoints

| Method | Route | Purpose | Protection |
| --- | --- | --- | --- |
| `GET` | `/media/{id}/{variant}` | Serve a public responsive derivative | resolved active public tenant and public media visibility |
| `POST` | `/tenant/website/media/upload` | Upload and catalogue a website image | authenticated tenant member with `website.media.manage` |
| `PATCH` | `/tenant/website/media/{id}/visibility` | Change `public` or `private` visibility | authenticated tenant member with `website.media.manage` |
| `DELETE` | `/tenant/website/media/{id}` | Soft-delete metadata and remove stored files | authenticated tenant member with `website.media.manage` |
| `GET` | `/tenant/website/media/{id}/private` | Serve an authorized original without shared caching | authenticated tenant member with `website.media.manage` |

## Scope Boundary

Phase 0 stops after readiness and hardening. Website configuration tables, public institutional pages, editorial content, gallery records, public management profiles, and Vue CMS islands belong to later approved phases and are not implemented here.

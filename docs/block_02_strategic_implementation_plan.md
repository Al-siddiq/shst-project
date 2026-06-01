# Block 2 Strategic Implementation Plan
## Public Website and Institutional Showcase

---

## Document Status

**Project:** SaaS Multi-Tenant Information Management System for Nigerian Schools of Health Sciences and Technology
**Block:** Block 2
**Block Name:** Public Website and Institutional Showcase
**Purpose:** Execution-ready implementation strategy for the current CodeIgniter 4 repository
**Backend:** CodeIgniter 4
**Frontend Enhancement:** Vue.js Composition API where interaction benefits from it
**Depends On:** Block 1 — SaaS and School Configuration
**Delivers:** A tenant-resolved, database-driven, mobile-first institutional public website and tenant-admin website CMS

---

# 1. Executive Summary

Block 2 turns the Block 1 SaaS foundation into a credible public website for every active tenant school. Every public request must resolve a tenant before tenant-owned records are queried. Every school-specific value shown to a public visitor must come from tenant-scoped database records or from a safe platform fallback that contains no school identity or school content.

The block has two surfaces:

1. **Public institutional showcase:** lightweight server-rendered pages for visitors, prospective applicants, parents, guardians, regulators, alumni, and partners.
2. **Tenant-admin website CMS:** authorized management screens and APIs for content editing, publishing, scheduling, archiving, media use, menu organization, dashboard review, and audit inspection.

The public showcase includes:

- public layout and navigation;
- homepage;
- about page;
- departments and programmes;
- admission information only;
- news;
- announcements;
- calendar notices;
- management profiles;
- gallery;
- contact page;
- portal and future application-link placeholders.

This block must **not** process applications, payments, students, staff, course registrations, course allocations, or results. It only provides public information and safe links or placeholders for later modules.

---

# 2. Source Documents Reviewed

This strategy is based on the complete documentation set in `docs/`:

| Document | Block 2 relevance |
| --- | --- |
| `Foundation-Concept-Documents.md` | Defines shared-schema tenancy, tenant-scoped ownership, tenant resolution, CI4 layering, two-layer authorization, layout rules, mobile-first UI, tenant themes, Vue request flow, audit logs, file visibility, lifecycle management, security, and the block-development contract. |
| `shst_saas_blueprint.md` | Establishes the product philosophy: one configurable SaaS platform, no school-specific hard-coding, and public website delivery before admission processing. |
| `block_01_SaaS_and_school_configuration.md` | Defines the Block 1 services and records Block 2 must reuse: tenants, profiles, departments, programmes, theme resolution, authorities, navigation foundations, and audit logging. |
| `block_02_public_website_roadmap.md` | Defines Block 2 scope, content types, CMS expectations, public routes, lifecycle states, security, audit events, and acceptance criteria. |
| `block_03_admission_roadmap.md` | Confirms that applicant identity, application submission, screening, admission decisions, and application workflow belong to Block 3. |
| `block_04_fees_payment_roadmap.md` | Confirms that fee configuration, invoices, payment gateways, receipts, and reconciliation belong to Block 4. |
| `block_05_records_roadmap.md` | Confirms that student and staff records belong to Block 5. |
| `block_06_academic_operations_roadmap.md` | Confirms that course allocation and student course registration belong to Block 6. |
| `block_07_results_roadmap.md` | Confirms that result computation, approvals, and publication belong to Block 7. |

---

# 3. Repository Baseline and Entry Gate

## 3.1 Existing Block 1 Foundations to Reuse

The current repository already provides the following useful Block 1 foundations:

| Existing foundation | Current implementation | Block 2 use |
| --- | --- | --- |
| Tenant request context | `TenantResolver`, `TenantContextManager`, and `TenantContextFilter` | Resolve public and admin tenant context before accessing website records. |
| Tenant-scoped model base | `TenantScopedModel` | Make every tenant-owned Block 2 model automatically constrain reads and inject `tenant_id` on insert. |
| Tenant branding | `ThemeResolver`, `TenantThemeModel`, and department identities | Apply tenant colors and logos to the public layout and department accents. |
| Institutional data | Tenant profile, department, and programme models | Reuse configured identity and academic showcase data rather than duplicating school-specific values. |
| Tenant authorization | `TenantAccessService`, `TenantAccessFilter`, IAM groups, operational authorities | Protect tenant-admin CMS routes and distinguish editing from publishing. |
| Audit service | `AuditLogger` and `AuditLogModel` | Record website configuration, content lifecycle, menu, and media actions. |
| API response convention | `ApiResponseTrait` | Keep tenant-admin CMS JSON responses consistent with Block 1. |

## 3.2 Required Entry-Gate Hardening Before Feature Work

Block 2 must begin by closing the following foundation gaps. These are prerequisites, not optional refinements.

### A. Add the file/media foundation

The design documents require public/private file visibility, but the current repository does not yet contain a media migration, media model, media service, or file access controller. Add them before gallery, logos, featured images, or management photos are implemented.

Required behavior:

- media metadata is stored in a tenant-owned `media_files` table;
- tenant-owned media is always scoped by `tenant_id`;
- storage paths are generated by the server and never accepted as arbitrary client filesystem paths;
- `visibility` supports at least `public` and `private`;
- only `public` files may be served by public website routes;
- private files require authenticated, authorized access;
- image MIME type, extension, file size, and dimensions are validated;
- safe derivative sizes are generated for low-bandwidth public delivery;
- uploads, replacements, visibility changes, and deletions are audited.

### B. Enforce active tenant visibility for the public website

The existing resolver locates tenant context, but public delivery also needs a publication-readiness check. A public tenant guard must deny or render a neutral unavailable page when the tenant is not active, the mapped domain is inactive, or the public website is disabled.

Required behavior:

- public pages render only for an active tenant;
- custom-domain resolution respects active domain mapping;
- disabled, suspended, deleted, or unresolved tenants do not leak tenant content;
- the unavailable response is neutral and contains no data from any other tenant;
- authenticated internal resolution remains separately controllable so platform support and tenant setup flows are not accidentally blocked.

### C. Complete actor and timestamp handling for tenant-owned mutations

Before Block 2 CRUD is added, ensure tenant-owned create and update operations consistently persist `created_by`, `updated_by`, `created_at`, and `updated_at`, and use soft deletion where configured. The CMS service layer must set actor identity explicitly and must not rely on clients to submit audit fields.

### D. Add CMS-specific operational authorities

Seed stable operational authority codes, and grant them through the existing membership-authority model:

- `website.dashboard.view`
- `website.settings.manage`
- `website.content.view`
- `website.content.create`
- `website.content.edit`
- `website.content.publish`
- `website.content.archive`
- `website.content.delete`
- `website.menu.manage`
- `website.media.manage`
- `website.audit.view`

A tenant super admin may receive all website authorities by policy. Other users receive only the authorities required for their CMS responsibilities. UI visibility is helpful, but controller and service checks remain mandatory.

---

# 4. Scope Contract

## 4.1 In Scope

Block 2 must deliver:

1. tenant-resolved public routes;
2. tenant-branded mobile-first public layout;
3. homepage configuration and homepage sections;
4. about page;
5. department listing and detail pages;
6. programme listing and detail pages;
7. public admission information page;
8. news listing and detail pages;
9. announcement listing and detail pages;
10. calendar notice listing and detail pages;
11. management profile page;
12. gallery page;
13. contact page;
14. public menu management;
15. website dashboard;
16. public/private media handling;
17. content lifecycle management;
18. content lifecycle audit trail;
19. tenant-admin authorization;
20. portal and future application-link placeholders;
21. public metadata and basic SEO fields;
22. tenant isolation, mobile behavior, and security tests.

## 4.2 Explicitly Out of Scope

Block 2 must not implement:

- applicant accounts;
- application forms or uploads;
- application submission;
- screening;
- admission decisions;
- application payments;
- school-fee payments;
- gateway integration;
- invoices or receipts;
- student profiles or student records;
- staff profiles or staff records;
- lecturer allocation;
- course registration;
- result entry, computation, approvals, or publication.

Admission CTAs must resolve to tenant-configured URLs or neutral placeholders. They must not create a hidden application workflow in Block 2.

---

# 5. Architectural Decisions

## 5.1 Public Delivery Pattern

Use **server-rendered CodeIgniter 4 views** for public pages. This is the preferred public delivery path because it:

- produces useful content without requiring a large JavaScript bundle;
- works well on low-bandwidth and intermittent mobile connections;
- supports straightforward page metadata and social sharing tags;
- gives a stable baseline when JavaScript is unavailable;
- keeps public content cacheable and simple to inspect.

Use Vue Composition API only where it adds clear value in the authenticated CMS, such as:

- dashboard counters and status filters;
- content editor forms;
- media picker and upload progress;
- menu ordering;
- publication scheduling controls;
- gallery ordering;
- preview-state controls.

Do not build the public website as a Vue SPA in Block 2.

## 5.2 Controller, Service, and Model Boundaries

Follow the repository's existing CI4 layering direction:

- **Controllers** validate request shape, invoke services, and format view or API responses.
- **Services** enforce tenant state, publication rules, authorization, safe media access, lifecycle transitions, and auditing.
- **Tenant-scoped models** read and write tenant-owned records only.
- **Views** render prepared view models and must not query the database.
- **Vue components** call CMS APIs and must not contain school-specific content or security decisions.

## 5.3 Tenant Resolution Rules

Public requests must resolve tenant context in this order:

1. active custom domain mapping;
2. tenant subdomain;
3. explicit `/t/{tenantSlug}` fallback route for development, previews, and controlled deployments;
4. reject or show a neutral unavailable page if no public tenant resolves.

Authenticated membership fallback is useful for tenant-admin routes but must not silently choose a public school website for a normal anonymous request.

## 5.4 Database-Driven Content Rule

Do not hard-code school names, addresses, telephone numbers, email addresses, mottos, admission requirements, management names, department descriptions, programme descriptions, news, announcements, calendar notices, images, gallery captions, portal URLs, or menu labels that belong to a school.

The only acceptable code-level defaults are neutral platform behavior defaults, such as:

- safe fallback colors;
- standard lifecycle codes;
- generic unavailable messages;
- generic placeholder labels when a configured URL is absent.

---

# 6. Data Model Strategy

## 6.1 Common Tenant-Owned Columns

Every new tenant-owned table must include:

- `id`
- `tenant_id`
- `created_by`
- `updated_by`
- `created_at`
- `updated_at`
- `deleted_at` where soft deletion applies

Tables that publish content should also include the relevant lifecycle metadata:

- `status`
- `published_at`
- `scheduled_for`
- `archived_at`
- `published_by`

Use foreign keys, composite tenant-aware indexes, unique constraints, slug indexes, and status/date indexes to support safe and efficient public reads.

## 6.2 Proposed Block 2 Tables

| Table | Purpose | Important fields |
| --- | --- | --- |
| `website_settings` | One tenant website configuration record | `site_title`, `tagline`, `motto`, `hero_title`, `hero_summary`, `hero_media_id`, `about_summary`, `about_body`, `mission`, `vision`, `history`, `contact_email`, `contact_phone`, `contact_phone_alt`, `address`, `map_embed_url`, `portal_url`, `application_info_url`, `application_cta_label`, `is_public_enabled`, `seo_title`, `seo_description`, social URLs |
| `website_menu_items` | Tenant-configurable public navigation | `parent_id`, `label`, `link_type`, `route_name`, `url`, `content_slug`, `target`, `sort_order`, `status`, `is_visible` |
| `website_content_items` | Shared editorial records for news, announcements, calendar notices, and optional reusable homepage notices | `content_type`, `title`, `slug`, `summary`, `body`, `featured_media_id`, `event_start_at`, `event_end_at`, `audience`, `status`, publication metadata, SEO fields |
| `department_public_profiles` | Public extension for Block 1 departments | `department_id`, `slug`, `summary`, `body`, `featured_media_id`, `status`, SEO fields |
| `programme_public_profiles` | Public extension for Block 1 programmes | `programme_id`, `slug`, `summary`, `body`, `admission_summary`, `requirements_body`, `featured_media_id`, `status`, SEO fields |
| `admission_information_pages` | Public-only admission guidance | `title`, `slug`, `summary`, `body`, `requirements_body`, `how_to_apply_body`, `application_link_label`, `application_url`, `status`, publication metadata, SEO fields |
| `management_profiles` | Public institutional management showcase, not Block 5 staff records | `full_name`, `title`, `bio`, `photo_media_id`, `sort_order`, `status`, SEO fields |
| `gallery_albums` | Public gallery grouping | `title`, `slug`, `description`, `cover_media_id`, `sort_order`, `status`, publication metadata |
| `gallery_items` | Ordered album images | `gallery_album_id`, `media_file_id`, `caption`, `alt_text`, `sort_order`, `status` |
| `media_files` | Tenant-owned media metadata and visibility | `original_name`, `stored_name`, `storage_disk`, `storage_path`, `mime_type`, `extension`, `size_bytes`, `width`, `height`, `visibility`, `category`, checksum, derivative metadata |

## 6.3 Reuse Existing Block 1 Records Instead of Duplicating Them

The public website must reuse:

- `tenant_profiles` for institutional identity and contact defaults;
- `tenant_themes` for tenant branding;
- `department_identities` and department colors for department accents;
- `departments` for public department identity;
- `programmes` for public programme identity;
- `audit_logs` for website audit history;
- membership and authority tables for CMS authorization.

The `department_public_profiles` and `programme_public_profiles` tables are extensions for optional public copy and media. They do not replace Block 1 academic records.

## 6.4 Why Use Public Extension Tables

Do not overload Block 1 academic setup tables with large CMS bodies. Academic configuration and public marketing copy have different lifecycle and publication needs. Extension tables allow a department or programme to remain operationally configured while its public page remains draft, scheduled, archived, or absent.

## 6.5 Content Type Boundaries

Use `website_content_items.content_type` only for closely related editorial content:

- `news`
- `announcement`
- `calendar_notice`
- `homepage_notice` if needed

Keep semantically distinct records in dedicated tables:

- management profiles;
- gallery albums and gallery items;
- admission information pages;
- public department profiles;
- public programme profiles;
- website settings;
- menu items;
- media files.

This avoids an over-generalized CMS while keeping editorial CRUD maintainable.

---

# 7. Content Lifecycle and Audit Trail

## 7.1 Lifecycle States

Use stable internal status codes:

| Status | Meaning | Publicly visible? |
| --- | --- | --- |
| `draft` | Work in progress | No |
| `scheduled` | Approved for a future publication time | Only when `scheduled_for <= now()` after transition or publish-query evaluation |
| `published` | Visible content | Yes, subject to `published_at <= now()` |
| `archived` | Retained but removed from public display | No |

Soft deletion is separate from archival. Normal public content should usually be archived rather than deleted.

## 7.2 Lifecycle Transition Rules

| From | Allowed transitions |
| --- | --- |
| `draft` | `scheduled`, `published`, soft-delete |
| `scheduled` | `draft`, `published`, `archived` |
| `published` | `draft`, `archived` |
| `archived` | `draft`, `published` |

Rules:

- an editor with `website.content.edit` may create and revise drafts;
- only a user with `website.content.publish` may publish or schedule public visibility;
- only a user with `website.content.archive` may archive;
- only a user with `website.content.delete` may soft-delete;
- service methods must validate transitions and never trust a submitted status blindly;
- scheduled publication may be evaluated by public queries initially and later moved to a CLI/cron command without changing the data model.

## 7.3 Audit Events

Use the existing audit service for at least:

- `website.settings.updated`
- `website.menu.created`
- `website.menu.updated`
- `website.menu.reordered`
- `website.menu.archived`
- `website.content.created`
- `website.content.updated`
- `website.content.scheduled`
- `website.content.published`
- `website.content.archived`
- `website.content.deleted`
- `website.department_profile.updated`
- `website.programme_profile.updated`
- `website.admission_information.updated`
- `website.management_profile.created`
- `website.management_profile.updated`
- `website.management_profile.archived`
- `website.gallery_album.created`
- `website.gallery_item.added`
- `website.gallery_item.removed`
- `website.media.uploaded`
- `website.media.visibility_changed`
- `website.media.deleted`
- `website.preview.opened` when preview access is sensitive

Audit metadata should include content type, record ID, old status, new status, relevant changed fields, actor, tenant, IP address, and user agent. Do not store private file bytes or unnecessary sensitive payloads inside audit metadata.

---

# 8. Public Website Information Architecture

## 8.1 Public Layout

Create `app/Views/layouts/public.php` with:

- tenant logo or neutral branding fallback;
- school title and optional tagline;
- mobile menu toggle;
- database-driven public navigation;
- portal access CTA;
- optional application-information CTA;
- tenant theme CSS variables;
- content slot;
- footer with tenant-configured contact details, useful links, and social links;
- basic SEO metadata and canonical URL support;
- accessible landmarks, keyboard-friendly navigation, and meaningful image alt text.

## 8.2 Required Public Pages

| Page | Route suggestion | Primary source |
| --- | --- | --- |
| Homepage | `/` and `/t/{tenantSlug}` | settings, menu, notices, featured content, public departments/programmes, tenant profile/theme |
| About | `/about` | website settings and tenant profile |
| Departments | `/departments` | active departments and published public extensions |
| Department detail | `/departments/{slug}` | active department plus public extension and its active programmes |
| Programmes | `/programmes` | active programmes and published public extensions |
| Programme detail | `/programmes/{slug}` | active programme plus public extension and department identity |
| Admission information | `/admissions` | published admission information page and placeholder/application URL configuration |
| News | `/news` | published `news` content items |
| News detail | `/news/{slug}` | one published `news` item |
| Announcements | `/announcements` | published `announcement` items |
| Announcement detail | `/announcements/{slug}` | one published `announcement` item |
| Calendar notices | `/calendar` | published `calendar_notice` items |
| Calendar notice detail | `/calendar/{slug}` | one published `calendar_notice` item |
| Management | `/management` | published management profiles |
| Gallery | `/gallery` and `/gallery/{slug}` | published albums, public gallery images |
| Contact | `/contact` | settings and tenant profile |
| Public media | `/media/{mediaId}/{safeName?}` | media service after tenant and visibility validation |

Equivalent `/t/{tenantSlug}/...` routes should exist where path-based resolution is needed. Generate URLs through one tenant-aware public URL helper so links do not accidentally switch tenant context.

## 8.3 Homepage Composition

The homepage should be assembled from tenant-scoped records and may contain:

- tenant-branded hero area;
- institution introduction;
- featured departments;
- featured programmes;
- current announcements;
- latest news;
- current calendar notices;
- admission-information CTA;
- portal-link CTA;
- selected gallery images;
- contact summary.

Do not assume every tenant has every section populated. Omit empty optional sections cleanly rather than rendering school-specific placeholder prose.

## 8.4 Department and Programme Visibility

A public department or programme page must require:

1. resolved active tenant;
2. active Block 1 department or programme record;
3. matching tenant scope;
4. a public extension in a public lifecycle state when detail marketing copy is needed.

A tenant may choose to list basic active academic records before extended profiles are completed, but unpublished extension bodies must never be exposed.

## 8.5 Admission Information Boundary

The admissions page may show:

- public guidance;
- entry requirements;
- how-to-apply instructions;
- deadline notices;
- contact details;
- a tenant-configured external or future-module application URL;
- a neutral “applications portal coming soon” placeholder if no URL is configured.

It must not collect applicant data or create applications.

---

# 9. Tenant-Admin Website CMS

## 9.1 Required CMS Pages

Add a `Website` area to tenant-admin navigation, controlled by authorities:

| Admin page | Purpose |
| --- | --- |
| Website dashboard | Setup completion, counts by lifecycle, scheduled items, recent audit activity, quick links |
| General settings | Homepage identity, hero, about content, contact details, social URLs, portal/application placeholders, metadata |
| Menu manager | Create, edit, hide, archive, nest, and reorder menu items |
| News manager | Draft, edit, preview, schedule, publish, archive news |
| Announcement manager | Manage announcements and audience labels |
| Calendar notice manager | Manage public calendar notices |
| Department showcase | Edit public department extensions while reusing Block 1 department records |
| Programme showcase | Edit public programme extensions while reusing Block 1 programme records |
| Admission information | Manage public admission guidance only |
| Management profiles | Manage public management showcase records |
| Gallery | Manage albums and ordered public images |
| Media library | Upload, classify, choose visibility, preview, and safely reuse media |
| Website audit trail | Inspect website-specific audit history |

## 9.2 Website Dashboard Metrics

Show tenant-scoped values only:

- public website enabled/disabled state;
- resolved public URL or domain status;
- website setup completion checklist;
- number of draft, scheduled, published, and archived content items;
- number of departments and programmes with completed public profiles;
- number of public gallery albums and images;
- scheduled publication queue;
- recent website audit events;
- missing essentials such as logo, contact email, contact phone, hero content, admission information, or menu items.

## 9.3 Vue Composition API Usage

Where Vue is introduced, create small CMS islands rather than a large SPA. Recommended composables:

- `useWebsiteDashboard()`
- `useContentEditor(contentType)`
- `useContentLifecycle()`
- `useMediaLibrary()`
- `useMenuManager()`
- `useGalleryManager()`
- `useWebsitePreview()`

Each composable should:

- call JSON endpoints protected by existing filters and authorities;
- show validation errors from the standard API response shape;
- display loading and retry states;
- avoid duplicating server authorization decisions;
- preserve usable forms and clear messages on narrow mobile screens.

---

# 10. Suggested CodeIgniter 4 Structure

```text
app/
├── Config/
│   ├── Routes.php
│   ├── Navigation.php
│   └── Services.php
├── Controllers/
│   ├── PublicSite/
│   │   ├── HomeController.php
│   │   ├── AboutController.php
│   │   ├── DepartmentController.php
│   │   ├── ProgrammeController.php
│   │   ├── AdmissionController.php
│   │   ├── ContentController.php
│   │   ├── ManagementController.php
│   │   ├── GalleryController.php
│   │   ├── ContactController.php
│   │   └── MediaController.php
│   └── Tenant/Website/
│       ├── DashboardController.php
│       ├── SettingsController.php
│       ├── MenuController.php
│       ├── ContentController.php
│       ├── DepartmentProfileController.php
│       ├── ProgrammeProfileController.php
│       ├── AdmissionInformationController.php
│       ├── ManagementProfileController.php
│       ├── GalleryController.php
│       ├── MediaController.php
│       └── AuditController.php
├── Filters/
│   └── PublicTenantFilter.php
├── Models/Tenant/Website/
│   ├── WebsiteSettingsModel.php
│   ├── WebsiteMenuItemModel.php
│   ├── WebsiteContentItemModel.php
│   ├── DepartmentPublicProfileModel.php
│   ├── ProgrammePublicProfileModel.php
│   ├── AdmissionInformationPageModel.php
│   ├── ManagementProfileModel.php
│   ├── GalleryAlbumModel.php
│   ├── GalleryItemModel.php
│   └── MediaFileModel.php
├── Services/Website/
│   ├── PublicTenantGuard.php
│   ├── PublicWebsiteService.php
│   ├── WebsiteSettingsService.php
│   ├── WebsiteContentService.php
│   ├── WebsiteMenuService.php
│   ├── WebsiteDashboardService.php
│   ├── WebsitePreviewService.php
│   └── MediaService.php
└── Views/
    ├── layouts/public.php
    ├── public_site/
    └── tenant/website/
```

If frontend bundling is introduced, place CMS source code under a clear asset source directory and generate versioned production assets. Do not copy school data into compiled JavaScript.

---

# 11. Routing and Authorization Plan

## 11.1 Public Routes

Attach tenant context and public-tenant guards to public pages:

```php
$routes->group('', ['filter' => 'tenantContext:required,publicTenant'], static function ($routes) {
    $routes->get('/', 'PublicSite\\HomeController::index');
    $routes->get('about', 'PublicSite\\AboutController::index');
    $routes->get('departments', 'PublicSite\\DepartmentController::index');
    $routes->get('departments/(:segment)', 'PublicSite\\DepartmentController::show/$1');
    $routes->get('programmes', 'PublicSite\\ProgrammeController::index');
    $routes->get('programmes/(:segment)', 'PublicSite\\ProgrammeController::show/$1');
    $routes->get('admissions', 'PublicSite\\AdmissionController::index');
    $routes->get('news', 'PublicSite\\ContentController::index/news');
    $routes->get('news/(:segment)', 'PublicSite\\ContentController::show/news/$1');
    $routes->get('announcements', 'PublicSite\\ContentController::index/announcement');
    $routes->get('announcements/(:segment)', 'PublicSite\\ContentController::show/announcement/$1');
    $routes->get('calendar', 'PublicSite\\ContentController::index/calendar_notice');
    $routes->get('calendar/(:segment)', 'PublicSite\\ContentController::show/calendar_notice/$1');
    $routes->get('management', 'PublicSite\\ManagementController::index');
    $routes->get('gallery', 'PublicSite\\GalleryController::index');
    $routes->get('gallery/(:segment)', 'PublicSite\\GalleryController::show/$1');
    $routes->get('contact', 'PublicSite\\ContactController::index');
});
```

Provide equivalent tenant-slug fallback groups where required. Keep any non-tenant platform landing page separate from tenant public routes.

## 11.2 Tenant-Admin CMS Routes

Attach:

- `protectedAuth`
- `tenantContext:required`
- `tenantAccess`
- the authority appropriate for each action

Examples:

- dashboard reads require `website.dashboard.view`;
- settings mutations require `website.settings.manage`;
- editorial create/update requires `website.content.create` or `website.content.edit`;
- publish endpoints require `website.content.publish`;
- archive endpoints require `website.content.archive`;
- menu mutations require `website.menu.manage`;
- media mutations require `website.media.manage`;
- website audit reads require `website.audit.view`.

Do not expose a single broad mutation endpoint that allows an editor to publish merely by posting `status=published`.

---

# 12. Media and File Visibility Plan

## 12.1 Storage Rules

- Store uploaded media outside directly executable public paths.
- Generate tenant-aware storage keys, such as `tenant/{tenantId}/website/{category}/{uuid}.{extension}`.
- Never use the original filename as the storage key.
- Keep the original filename as metadata only.
- Serve public media through a controller or a deliberately generated public derivative mechanism after verifying tenant, visibility, and file status.
- Serve private media only after authenticated authorization.
- Prevent path traversal and content-type confusion.

## 12.2 Public Media Categories

Support at least:

- `tenant_logo`
- `homepage_hero`
- `content_featured_image`
- `department_image`
- `programme_image`
- `management_photo`
- `gallery_image`

## 12.3 Performance Rules

- generate compressed thumbnails and responsive sizes;
- lazy-load below-the-fold images;
- set image dimensions to reduce layout shift;
- paginate content and gallery listings;
- avoid autoplay video and oversized homepage carousels;
- use cache headers and tenant-aware cache keys;
- invalidate affected tenant cache entries on publish, archive, settings update, menu change, or public media replacement.

---

# 13. Validation and Security Plan

## 13.1 Tenant Isolation

For every tenant-owned model and query:

- extend `TenantScopedModel` or apply an equivalent mandatory tenant scope;
- never accept a client-submitted `tenant_id` as authority;
- resolve related department, programme, media, album, and parent-menu records through tenant-scoped models;
- ensure foreign records belong to the current tenant before linking them;
- include cross-tenant negative tests for read and write endpoints;
- include media URL cross-tenant negative tests.

## 13.2 Content Validation

Validate:

- required title, slug, and type fields;
- tenant-unique slugs within relevant content scope;
- allowed lifecycle transitions;
- scheduled timestamps;
- valid and sanitized rich text;
- permitted URL schemes for portal, application, map, and social links;
- menu nesting depth and allowed link targets;
- public profile relationship ownership;
- image MIME types, extensions, sizes, and dimensions;
- alt text for meaningful public images;
- event date ordering for calendar notices.

## 13.3 Rendering Safety

- escape plain text in views;
- sanitize rich text through a dedicated server-side policy before storage or rendering;
- do not render arbitrary scripts, inline event handlers, iframes, or unsafe URLs from CMS fields;
- treat `map_embed_url` as a constrained provider URL or render it as an external map link initially;
- use CSRF protection for authenticated CMS mutations;
- keep public routes read-only;
- return neutral 404/unavailable views for unpublished or cross-tenant content.

## 13.4 Preview Safety

If draft preview is implemented:

- require an authenticated tenant member with `website.content.view`;
- scope preview tokens to tenant, content ID, actor, and expiry;
- do not use predictable public preview URLs;
- audit preview access where appropriate;
- ensure preview responses are not cached publicly.

---

# 14. Mobile-First and Nigeria-Aware UI Plan

## 14.1 Public UI Priorities

Design first for a narrow mobile viewport and uncertain bandwidth:

- readable typography;
- simple header and collapsible menu;
- prominent contact and admission-information actions;
- compact content cards;
- limited image weight;
- no essential interaction hidden behind hover;
- visible loading and empty states;
- paginated listings;
- touch-friendly controls;
- semantic HTML and keyboard support.

## 14.2 Tenant-Admin CMS Priorities

- use stacked forms on narrow screens;
- keep lifecycle action buttons explicit;
- avoid wide tables where cards or responsive lists work better;
- show upload progress and file-size validation clearly;
- make draft/published status visually distinct without relying on color alone;
- keep confirmation prompts for publish, archive, delete, and visibility changes;
- provide preview links that preserve tenant context.

---

# 15. Phased Implementation Work Plan

## Phase 0 — Block 1 Readiness and Hardening

**Goal:** Ensure Block 2 does not build on missing or unsafe foundations.

Tasks:

1. add file/media migration, model, service, storage policy, and access controller;
2. add public active-tenant guard and neutral unavailable response;
3. complete tenant-owned mutation actor/timestamp handling;
4. seed CMS authorities and assign tenant-super-admin defaults;
5. verify existing tenant scope behavior for Block 2 relationships;
6. document and test public-domain, subdomain, and tenant-slug resolution behavior.

**Exit criteria:** public file visibility and public tenant state checks are enforced by tests.

## Phase 1 — Public Website Core

**Goal:** Establish a tenant-branded public shell and settings source.

Tasks:

1. create `website_settings` and `website_menu_items` migrations and models;
2. add website settings service and menu service;
3. create public layout;
4. create tenant-aware public URL helper;
5. implement public homepage, about, and contact pages;
6. add tenant theme CSS variables and mobile navigation;
7. add neutral empty states and unavailable page;
8. add cache-key strategy scoped by tenant.

**Exit criteria:** two active tenants render different database-driven branding and settings without leakage.

## Phase 2 — Department, Programme, and Admission Showcase

**Goal:** Reuse Block 1 academic structure while adding public copy safely.

Tasks:

1. add department and programme public extension tables;
2. add admission-information table;
3. create services and public routes for department/programme listings and detail pages;
4. add tenant-admin forms for public extensions;
5. implement admission guidance and placeholder/application URL behavior;
6. apply department color identity to public cards and detail pages;
7. test inactive and cross-tenant academic records.

**Exit criteria:** a tenant can showcase configured departments and programmes and publish admission guidance without processing an application.

## Phase 3 — Editorial Content and Lifecycle

**Goal:** Deliver auditable editorial publishing.

Tasks:

1. add `website_content_items` migration and model;
2. add news, announcement, and calendar-notice services;
3. implement draft, schedule, publish, archive, and soft-delete service commands;
4. implement CMS forms and Vue islands where useful;
5. add public listing and detail routes;
6. add homepage content aggregation;
7. audit every lifecycle transition;
8. add pagination and publication-time tests.

**Exit criteria:** only published or due scheduled content appears publicly; editor and publisher permissions differ correctly.

## Phase 4 — Management Profiles, Gallery, and Media Library

**Goal:** Complete the institutional showcase with public imagery.

Tasks:

1. add management-profile, gallery-album, and gallery-item tables;
2. implement media picker and safe reuse;
3. add public management and gallery pages;
4. add ordered gallery controls;
5. generate responsive image derivatives;
6. enforce public/private visibility through media controller tests;
7. audit gallery and media mutations.

**Exit criteria:** public pages can display optimized public images, while private or cross-tenant media remains inaccessible.

## Phase 5 — CMS Dashboard, Menu Manager, Audit View, and Stabilization

**Goal:** Make the module operational for tenant administrators.

Tasks:

1. implement website dashboard metrics and checklist;
2. implement Vue-enhanced menu ordering;
3. add website-specific audit filtering;
4. add setup completion indicators;
5. validate public metadata fields and canonical URLs;
6. test responsive behavior and accessibility basics;
7. add tenant-specific cache invalidation;
8. run full regression tests and document manual checks.

**Exit criteria:** each authorized tenant can configure, publish, inspect, and maintain its own website independently.

---

# 16. Testing Strategy

## 16.1 Automated Test Categories

### Tenant resolution and availability

- custom domain resolves the correct tenant;
- subdomain resolves the correct tenant;
- tenant-slug fallback resolves the correct tenant;
- unresolved tenant gets a neutral response;
- suspended or disabled tenant website is unavailable;
- inactive domain mapping is unavailable publicly.

### Tenant isolation

- tenant A cannot read tenant B settings;
- tenant A cannot update tenant B content;
- tenant A cannot link tenant B media, department, programme, gallery album, or menu parent;
- tenant A public pages never show tenant B content;
- tenant A media URL cannot retrieve tenant B file.

### Authorization

- unauthenticated users cannot access CMS routes;
- tenant member without website authority cannot manage website records;
- editor can save drafts but cannot publish;
- publisher can publish and archive;
- audit viewer can inspect website audit logs without mutation access.

### Lifecycle

- drafts never appear publicly;
- future scheduled items do not appear early;
- due scheduled items appear according to service policy;
- archived and deleted items do not appear publicly;
- invalid transitions fail clearly;
- publication and archival events are audited.

### Media

- allowed image upload succeeds;
- invalid MIME type, extension, and oversized upload fail;
- public derivative is accessible for the owning tenant;
- private file is denied on a public route;
- path traversal attempts fail;
- deleted or missing file returns a neutral response.

### Public pages

- empty optional sections are omitted cleanly;
- homepage content is tenant-scoped;
- department and programme relationships are tenant-scoped;
- admission page uses a configured URL or neutral placeholder only;
- metadata and canonical URL are tenant-aware;
- menus are tenant-configured and ordered correctly.

### Mobile and performance

- public layout is usable at narrow viewport widths;
- CMS forms remain usable at narrow viewport widths;
- gallery and editorial listings paginate;
- public images use optimized derivatives;
- tenant-aware cache entries invalidate after publication changes.

## 16.2 Manual Verification Checklist

Use at least two demo tenants with visibly different themes, profile data, content, and files. Verify:

1. each hostname and slug path displays only its own tenant;
2. menus differ correctly by tenant;
3. tenant A cannot discover tenant B draft URLs or media URLs;
4. an editor cannot publish;
5. a publisher can publish and audit records capture the change;
6. admission CTA never accepts applicant data;
7. public pages remain usable on a small mobile viewport and a throttled connection;
8. private media never renders publicly.

---

# 17. Definition of Done

Block 2 is complete only when:

1. every active, public-enabled tenant can render an independent public website;
2. all school-specific public content loads from tenant-scoped database records;
3. tenant theme and department identity data are reused from Block 1;
4. homepage, about, departments, programmes, admissions information, news, announcements, calendar notices, management profiles, gallery, and contact pages exist;
5. public menus are tenant-managed from database records;
6. tenant admins have a website dashboard and authorized CMS screens;
7. draft, scheduled, published, and archived lifecycle behavior is enforced server-side;
8. publish, archive, menu, settings, gallery, and media actions are audited;
9. public/private media visibility is enforced;
10. automated tests prove tenant isolation for content and files;
11. public pages are mobile-first and lightweight;
12. application processing, payments, student records, staff records, course allocation, course registration, and results remain absent.

---

# 18. Final Block 2 Delivery Summary

After Block 2, every active tenant school should be able to present itself online with its own branding, departments, programmes, admission information, news, announcements, calendar notices, gallery, management profiles, and contact details.

The school becomes visible and credible to the public through a tenant-resolved, database-driven public website. Authorized tenant administrators can manage public content through an auditable CMS, and public media visibility remains safe and tenant-isolated.

Block 2 intentionally stops at public showcase and information delivery. It does not process applications, payments, students, staff, course allocation, course registration, or results.

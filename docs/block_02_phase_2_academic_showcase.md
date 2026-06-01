# Block 2 Phase 2 Delivery Note
## Department, Programme, and Admission Showcase

Phase 2 reuses Block 1 departments and programmes while adding optional tenant-owned public extensions. Internal academic configuration remains separate from public presentation, allowing a school to configure operations without exposing incomplete website copy.

## Delivered Showcase Foundations

### Public department profiles

`department_public_profiles` adds tenant-controlled slug, summary, body, image reference, metadata, and publication status to a Block 1 department. Public listings and details require both a published extension and an active tenant-owned department. Department identity colors are applied to cards and detail headers.

### Public programme profiles

`programme_public_profiles` adds tenant-controlled slug, summary, body, entry requirements, career opportunities, duration explanation, award type, admission availability, image reference, metadata, and publication status to a Block 1 programme. Public listings and details require an active tenant-owned programme, an active owning department, and a published extension.

### Public admission guidance

`admission_information_pages` stores tenant-controlled public guidance, general requirements, fee notes, screening details, required documents, important dates, how-to-apply instructions, and an optional application URL. The public admissions page renders a neutral placeholder when no URL is configured. It never collects applicant data or processes an application.

### Tenant-admin forms

Authorized tenant users with `website.content.edit` can maintain department profiles, programme profiles, and public admission guidance through server-rendered forms. Publishing additionally requires `website.content.publish`. Mutations are audited and affected tenant cache entries are invalidated.

## Public Routes

| Method | Route | Purpose |
| --- | --- | --- |
| `GET` | `/departments` | Published department listing |
| `GET` | `/departments/{slug}` | Published department detail and its published programmes |
| `GET` | `/programmes` | Published programme listing with optional department filter |
| `GET` | `/programmes/{slug}` | Published programme detail |
| `GET` | `/admissions` | Public admission guidance and optional portal link |

Equivalent `/t/{tenantSlug}/...` paths support route-slug deployments.

## Tenant-Admin Routes

| Method | Route | Purpose |
| --- | --- | --- |
| `GET`, `POST` | `/tenant/website/showcase/departments` | View and save department public extensions |
| `GET`, `POST` | `/tenant/website/showcase/programmes` | View and save programme public extensions |
| `GET`, `POST` | `/tenant/website/showcase/admissions` | View and save public admission guidance |

## Scope Boundary

Phase 2 stops after academic and admission-information showcase delivery. It does not process applications, fees, payments, students, staff, course allocation, course registration, results, editorial news, announcements, calendar notices, gallery records, management profiles, or Vue CMS islands.

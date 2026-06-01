# Block 2 Phase 5 Delivery Note
## CMS Dashboard, Menu Manager, Audit View, and Stabilization

Phase 5 makes the Block 2 public website operational for tenant administrators. It adds tenant-only readiness metrics, settings maintenance, menu management, website audit filtering, canonical-host validation, explicit cache invalidation, and stabilization checks.

## Delivered Operations

- website dashboard metrics, setup percentage, missing-essential checklist, scheduled queue, and recent website audit events;
- tenant settings form with HTTP(S)-only URL validation, metadata fields, public hero-media checks, audit logging, and settings-cache invalidation;
- tenant menu create, edit, hide, archive, one-level nesting, and reorder commands with cross-tenant parent checks and menu-cache invalidation;
- progressively enhanced Vue Composition API menu-ordering island with loading, retry, accessible status messaging, JSON response handling, and a server-rendered fallback;
- website-only tenant audit history filtering;
- canonical primary-domain hostname validation before public metadata renders.

## Manual Stabilization Checklist

1. At 320px width, verify public navigation, cards, galleries, and tenant CMS forms remain usable without horizontal page overflow.
2. Navigate public and CMS pages by keyboard; verify skip links, menu buttons, reorder buttons, labels, and focus states remain usable.
3. Disable JavaScript and verify public pages plus server-rendered settings, menu, gallery, and editorial forms remain usable.
4. Upload public and private images; confirm only public derivatives work anonymously and private CMS previews use `private, no-store` caching.
5. Configure a valid primary domain and verify canonical URLs use HTTPS; configure an invalid domain fixture and verify canonical generation falls back safely.
6. Publish records for two tenants and confirm each hostname or `/t/{slug}` website renders only its own settings, menu, content, media, and audit events.

## Scope Boundary

Phase 5 completes Block 2. It does not implement application processing, fees, payments, student records, staff records, course allocation, course registration, or results.

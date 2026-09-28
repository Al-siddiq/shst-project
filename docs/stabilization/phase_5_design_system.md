# Phase 5 — Internal UI convention

Blocks 1–3 remain server rendered with progressive Vue enhancement. Internal
screens use `public/assets/css/application-ui.css`; tenant public and applicant
screens use `public/assets/css/public-site.css`. Modules must reuse semantic
classes (`page-head`, `panel`, `metric-grid`, `action-grid`, `table-wrap`,
`badge`, `notice`, `error`, and `empty`) instead of adding page-local CSS.

Branding is expressed through CSS custom properties. Public tenant values map to
`--tenant-primary`, `--tenant-secondary`, and `--tenant-accent`; internal tokens
use the `--ui-*` namespace. Page-specific arbitrary branding CSS is prohibited.

## Interaction rules

- Every input has a visible label; errors are adjacent and understandable.
- Primary, secondary, dangerous, disabled, loading, success, error, and empty
  states are explicit. Color is not the only status signal.
- Keyboard focus remains visible. Pages include a skip link and semantic landmarks.
- Wide data uses a labelled mobile-card rendering where appropriate or an explicit
  horizontal scroll container—never viewport overflow.
- JavaScript may enhance ordering/autosave/upload behavior, but the server form is
  the source of truth and the supported workflow must remain recoverable.
- Navigation is permission resolved on the server, mobile operable, and marks the
  current section. Unsupported future modules are absent.
- Every download, print, export, or generate control needs an implemented route,
  authorization policy, response, failure state, and acceptance test.

## Build contract

Vite has `publicDir: false` because its output lives under `public/assets/js`.
This prevents recursive copying of the output directory. Production builds must
run `npm run build` and deploy the generated entry files with the static CSS and
small no-build fallback scripts.

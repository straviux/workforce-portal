# Codebase Guide

## What This Is

Staff-only portal for managing employee fund transactions (OBR/DV/Payroll), certifications, and SWA (Special Work Assignment) accomplishment reports. No employee self-service — only assigned staff have access. Standalone Laravel app deployed at `workforce.yakapsaedukasyon.com`, sharing the `scholarship_program_devmode` MySQL database and root-domain session cookie with the sibling project `../scholarship-sys` (log in on either, both recognize the session). See `instructions/Workforce Portal — Implementation Plan.md` for the original scaffolding plan and coding-standard rationale; treat it as historical context, not a live checklist — the app has since diverged (own `WorkforceLayout.vue`, PrimeVue `Dialog` instead of `IosModal`, PHPUnit instead of Pest).

## Commands

- Dev (vite + artisan serve together): `npm run startlocal` (serves PHP on port 8002) — or the full stack via `composer run dev` (server + queue listener + `pail` log tailer + vite, concurrently)
- Frontend build: `npm run build`
- Tests (PHPUnit, not Pest): `php artisan test` or `./vendor/bin/phpunit`
  - Single test: `php artisan test --filter="test name"` or `php artisan test tests/Feature/SwaWorkflowTest.php`
- Lint/format PHP: `./vendor/bin/pint` (single file: `./vendor/bin/pint path/to/file.php`)

## Stack

Laravel 13 + Inertia.js v2 + Vue 3 (Composition API) + PrimeVue 4 + Tailwind CSS 4 + DaisyUI (via Vite). Ziggy exposes named routes to JS via `route()`. Spatie laravel-permission handles roles/permissions, gated per-route with `check.permission:<name>` middleware. `maatwebsite/excel` for exports, `simplesoftwareio/simple-qrcode` for QR codes on certs/reports.

## Domain Model

- `Employee` — staff record, distinguished by `employee_type` (`contract_of_service` vs `project_based`). COS employees carry extra fields (`contract_ref_no`, `swa` flag, `atm_account_no`, `monthly_compensation`, deductions); project-based employees only use the common subset (name, address, amount, office, responsibility center, account code).
- `EmployeeFundTransaction` — OBR/DV/Payroll voucher per employee, with `transaction_status` (pending → approved/active/denied/suspended) and PDF generation endpoints (`dv-pdf`, `obr-pdf`, `payroll-pdf`).
- `ResponsibilityCenter` / `Particular` — shared budget codes, same model shape as `scholarship-sys`.
- `Certification` — one-off issued certificates (non-ROS certs), carries the subject's name/designation/office and an embedded signatory block.
- SWA (accomplishment reporting) module — polymorphic across two subject types via `subject_type`/`subject_id`: a personal SWA (subject = `User`, the logged-in staff member) and an employee SWA (subject = `Employee`, COS staff only). Chain: `SwaTask` (a subject's standing list of task templates, ordered by `sort_order`) → `SwaReport` (a generated report for a date range: `period_start_date`/`period_end_date`, `work_days`) → `SwaReportTask` (snapshot of tasks included in that report) → `SwaReportTaskDailyValue` (per-task, per-day accomplishment entries). Routes split as `/swa/personal/*` vs `/swa/employees/{id}/*` in `routes/api.php`; both funnel through `SwaService`.
- `Signatory` — reusable signature blocks with a fixed `part` (A = Office Head prepares/verifies, B = Accountant certifies, C = Treasurer confirms funds, D = Governor approves). `Certification` and `SwaReport` both embed a chosen signatory's name/office/titles at generation time (`signatory_*` columns) rather than referencing it live, so edits to a `Signatory` don't retroactively change already-issued documents — except `office_head_signatory_id`, which stays a live FK.
- `CalendarEvent` — shared office calendar, permission-gated separately from other modules.

## Routing Pattern

`routes/web.php` only renders Inertia pages (`inertia('Module/index')`) behind `check.permission:<module>.view`; it holds no business logic. All data operations go through `routes/api.php` under `App\Http\Controllers\Api\*`, grouped under `middleware(['web', 'auth'])` with per-action `check.permission:<module>.manage|delete`. Both apps share the `auth` guard via the shared session cookie — there is no separate API token auth.

## UI Conventions

- Modals use PrimeVue's `<Dialog modal :draggable="false">` directly (no custom modal-shell component here, unlike `scholarship-sys`'s `IosModal.vue`) — see `Pages/Swa/Modal/SwaWorkspace.vue` for the pattern. Content inside still uses the `ios-section`/`ios-card` utility classes from `resources/css/ios-design-system.css` (copied from `scholarship-sys`) for visual consistency.
- Tailwind utility classes + PrimeVue components only. PrimeIcons (`pi pi-*`) only — no other icon sets.
- `WorkforceLayout.vue` (`resources/js/Layouts/`) is the sole authenticated layout — sidebar nav driven by `Components/ui/navigation/SidebarLink.vue`.
- `created_by`/`updated_by` are auto-set from `Auth::id()` in model `boot()` hooks (`creating`/`updating`), not in controllers — follow this pattern for new models rather than setting them per-request.

## Other Notable Pieces

- `resources/js/Composables/usePdfPrint.js` — shared client-side PDF/print handling, used by Certification and Fund Transaction PDF templates (`Pages/*/Pdf/`).
- `scholarship-sys` (sibling repo) is the reference implementation for shared patterns (models like `FundTransaction`, `ResponsibilityCenter`; the `AdminLayout` this app's `WorkforceLayout` was adapted from) — check there when a pattern here seems incomplete, but confirm it still matches since both apps evolve independently.

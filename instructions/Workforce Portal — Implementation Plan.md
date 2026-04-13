# Workforce Portal — Implementation Plan

## Overview
A standalone Laravel 13 + Inertia.js + Vue 3 project deployed at `workforce.yakapsaedukasyon.com`.
Manages employee/staff fund transactions (OBR, DV, Payroll). Only assigned staff have access — no employee self-service.
Shares the existing `scholarship_program_devmode` MySQL database via direct connection and shared root-domain session cookie for seamless auth.

---

## Project Location
```
SCHOLARSHIP PROGRAM/
├── scholarship-sys/        ← existing project (reference)
└── workforce-portal/       ← this project
```

---

## Tech Stack
- Laravel 13
- Inertia.js v2
- Vue 3 (Composition API)
- PrimeVue 4 (UI components)
- Tailwind CSS + DaisyUI
- Vite
- Spatie Laravel Permission (RBAC)
- MySQL — shared DB: `scholarship_program_devmode`

---

## How would I like you to react

- Act as my CTO. You must push back when necessary. You do not need to be a people pleaser. You need to make sure we succeed.
- First, confirm understanding in 1-2 sentences.
- Default to high-level plans first, then concrete next steps.
- When uncertain, ask clarifying questions instead of guessing. [This is critical]
- Use concise bullet points. Link directly to affected files / DB objects. Highlight risks.
- When proposing code, show minimal diff blocks, not entire files.
- When SQL is needed, wrap in sql with UP / DOWN comments.
- Suggest automated tests and rollback plans where relevant.
- Keep responses under ~400 words unless a deep dive is requested.

---

## Our Workflow

1. We brainstorm on a feature or I tell you a bug I want to fix
2. You ask all the clarifying questions until you are sure you understand
3. You create a discovery prompt gathering all the information you need to create a great execution plan (including file names, function names, structure and any other information)
4. You can ask for any missing information I need to provide manually
5. You break the task into phases (if not needed just make it 1 phase)

---

## Phase 1 — Project Scaffolding

- [ ] From `SCHOLARSHIP PROGRAM/` folder: `composer create-project laravel/laravel workforce-portal`
- [ ] Install backend packages:
  ```
  composer require inertiajs/inertia-laravel tightenco/ziggy spatie/laravel-permission laravel/sanctum maatwebsite/excel simplesoftwareio/simple-qrcode spatie/browsershot
  ```
- [ ] Install frontend packages:
  ```
  npm install vue@3 @inertiajs/vue3 @vitejs/plugin-vue primevue@4 primeicons @primevue/themes tailwindcss @tailwindcss/vite daisyui axios
  ```
- [ ] Copy and adapt from `scholarship-sys/`:
  - `vite.config.js`
  - `tailwind.config.js`
  - `jsconfig.json`
  - `postcss.config.js`
  - `resources/css/ios-design-system.css`
  - `resources/js/app.js` (Inertia bootstrap)
  - `resources/js/bootstrap.js` (Axios config)

---

## Phase 2 — Environment Configuration

- [ ] `.env` settings:
  ```env
  APP_NAME="Workforce Portal"
  APP_URL=https://workforce.yakapsaedukasyon.com

  DB_DATABASE=scholarship_program_devmode
  # Same DB_HOST, DB_USERNAME, DB_PASSWORD as scholarship-sys

  SESSION_DRIVER=database
  SESSION_DOMAIN=.yakapsaedukasyon.com
  SESSION_COOKIE=          # Must be the EXACT same value as scholarship-sys
  ```
- [ ] `config/session.php` — set `domain` to `.yakapsaedukasyon.com`
- [ ] `php artisan key:generate`

> **Critical:** `SESSION_COOKIE` must match the main app exactly so shared login works.

---

## Phase 3 — Auth & Shared Models

- [ ] Scaffold Breeze (Inertia/Vue): `php artisan breeze:install vue`
  - Remove registration routes/views — keep only login, logout
- [ ] Copy these models from `scholarship-sys/app/Models/` (no migrations — tables already exist in shared DB):
  - `User.php`
  - `ResponsibilityCenter.php`
  - `Particular.php`
  - `Role.php`
  - `SystemOption.php`
- [ ] Copy middleware from `scholarship-sys/app/Http/Middleware/`:
  - `CheckRole.php`
  - `CheckPermission.php`
- [ ] Register middleware aliases in `bootstrap/app.php`
- [ ] Create `WorkforceLayout.vue` in `resources/js/Layouts/` — adapt `AdminLayout.vue` from main project with workforce branding

---

## Phase 4 — Database Migration

- [ ] `php artisan make:migration create_employee_fund_transactions_table`

### Table: `employee_fund_transactions`

**Common columns (all employee types):**

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigIncrements | PK |
| `transaction_id` | string, unique | Human-readable ref |
| `employee_type` | enum: `contract_of_service`, `project_based` | |
| `payee_name` | string | |
| `payee_address` | string | |
| `office` | string | |
| `responsibility_center` | unsignedBigInteger, FK | → responsibility_centers.id |
| `account_code` | string, nullable | |
| `particulars_name` | string, nullable | |
| `particulars_description` | text, nullable | HTML from Quill |
| `amount` | decimal(15,2) | |
| `fiscal_year` | string, nullable | |
| `disbursement_type` | string, nullable | |
| `explanation` | text, nullable | |
| `obr_type` | string, nullable | |
| `obr_no` | string, nullable | |
| `dv_no` | string, nullable | |
| `date_obligated` | date, nullable | |
| `transaction_status` | string, default `pending` | pending\|approved\|active\|denied\|suspended |
| `remarks` | text, nullable | HTML from Quill |
| `upload_token` | string, nullable | |
| `upload_token_expires_at` | timestamp, nullable | |
| `created_by` | unsignedBigInteger, nullable | FK → users.id |
| `updated_by` | unsignedBigInteger, nullable | FK → users.id |
| `deleted_at` | timestamp, nullable | SoftDeletes |
| `timestamps` | | created_at, updated_at |

**Contract of Service only (nullable for project-based):**

| Column | Type | Notes |
|--------|------|-------|
| `employee_id` | string, nullable | Employee ID number |
| `contract_ref_no` | string, nullable | |
| `swa` | boolean, default false | Special Work Assignment |
| `atm_account_no` | string, nullable | |
| `monthly_compensation` | decimal(15,2), nullable | |
| `deduction_sss` | decimal(15,2), nullable | |
| `deduction_philhealth` | decimal(15,2), nullable | |
| `deduction_hdmf` | decimal(15,2), nullable | |

> **Project-based** employees only use: `payee_name`, `payee_address`, `amount`, `office`, `responsibility_center`, `account_code`. All COS-only columns stay nullable.

> **Not included:** `academic_year`, `semester` — not applicable for employees.

- [ ] `php artisan migrate`

---

## Phase 5 — Backend

### Model
- [ ] `app/Models/EmployeeFundTransaction.php`
  - `$fillable` — all columns listed above
  - `$casts`: `swa => 'boolean'`, `amount/monthly_compensation/deduction_* => 'decimal:2'`, `upload_token_expires_at => 'datetime'`
  - `use SoftDeletes`
  - `boot()` — auto-set `created_by` / `updated_by` from `Auth::id()`
  - Relationship: `responsibilityCenter()` → `belongsTo(ResponsibilityCenter::class, 'responsibility_center', 'id')`

### Service
- [ ] `app/Services/EmployeeFundTransactionService.php`
  - `create(array $data)` — wrapped in `DB::transaction()`
  - `update(EmployeeFundTransaction $record, array $data)`
  - `updateStatus(EmployeeFundTransaction $record, string $status)`
  - `delete(EmployeeFundTransaction $record)`

### Controller
- [ ] `app/Http/Controllers/Api/EmployeeFundTransactionController.php`
  - `index` — paginated list with filters (search, status, fiscal_year, responsibility_center)
  - `store` — delegates to Service
  - `show` — single record
  - `update` — delegates to Service
  - `updateStatus` — PATCH
  - `destroy` — soft delete
  - `generateDVPdf`
  - `generateOBRPdf`
  - `generatePayrollPdf`
  - Always `$this->authorize()` per action

### FormRequests
- [ ] `StoreEmployeeFundTransactionRequest`
  - Conditional rules: if `employee_type === 'contract_of_service'`, require COS-only fields
- [ ] `UpdateEmployeeFundTransactionRequest`

### Routes

`routes/api.php` — under `middleware(['web', 'auth'])`:
```php
Route::post('/employee-fund-transactions', [EmployeeFundTransactionController::class, 'store']);
Route::get('/employee-fund-transactions', [EmployeeFundTransactionController::class, 'index']);
Route::get('/employee-fund-transactions/{id}', [EmployeeFundTransactionController::class, 'show']);
Route::put('/employee-fund-transactions/{id}', [EmployeeFundTransactionController::class, 'update']);
Route::patch('/employee-fund-transactions/{id}/update-status', [EmployeeFundTransactionController::class, 'updateStatus']);
Route::delete('/employee-fund-transactions/{id}', [EmployeeFundTransactionController::class, 'destroy']);
Route::get('/employee-fund-transactions/{id}/dv-pdf', [EmployeeFundTransactionController::class, 'generateDVPdf']);
Route::get('/employee-fund-transactions/{id}/obr-pdf', [EmployeeFundTransactionController::class, 'generateOBRPdf']);
Route::get('/employee-fund-transactions/{id}/payroll-pdf', [EmployeeFundTransactionController::class, 'generatePayrollPdf']);
```

`routes/web.php` — Inertia page:
```php
Route::get('/employee-fund-transactions', fn() => inertia('EmployeeFundTransactions/index'))
    ->middleware(['auth', 'check.permission:employee_fund_transactions.view'])
    ->name('employee_fund_transactions.index');
```

---

## Phase 6 — Frontend

### Layout
- [ ] `resources/js/Layouts/WorkforceLayout.vue`
  - Adapt `AdminLayout.vue` from `scholarship-sys`
  - Update app name/branding
  - Keep same sidebar structure and iOS design system classes (`bg-[#222831]`, frosted glass sidebar, `.content-bg`)

### Main Page
- [ ] `resources/js/Pages/EmployeeFundTransactions/index.vue`
  - Adapt `FundTransactions/index.vue` from `scholarship-sys`
  - VoucherWizard with two-path form toggled by `employee_type`:
    - **Contract of Service** — full field set (employee_id, office, address, responsibility_center, account_code, particulars, contract_ref_no, swa, atm_account_no, monthly_compensation, deductions)
    - **Project-based** — reduced set (name, address, amount, office, responsibility_center, account_code)
  - DataTable with employee-focused columns

### Modals (`Pages/EmployeeFundTransactions/Modal/`)
- [ ] `ViewTransactionModal.vue` — conditionally show COS-only section based on `employee_type`
- [ ] `DeleteConfirmModal.vue`
- [ ] `FileUploadModal.vue`
- [ ] `RemarksModal.vue` — Quill editor (text-only toolbar: bold, italic, underline, ordered list, bullet list, clean)
- [ ] `StatusModal.vue`
- [ ] `QrCodeModal.vue`
- [ ] `TrackingHistoryModal.vue`

> Reference: `scholarship-sys/resources/js/Pages/FundTransactions/Modal/`

### PDF Templates (`Pages/EmployeeFundTransactions/Pdf/`)
- [ ] `DvTemplate.vue`
- [ ] `ObrTemplate.vue`
- [ ] `PayrollTemplate.vue`

> Reference: `scholarship-sys/resources/js/Pages/FundTransactions/Pdf/`

### Shared Composables & Components
- [ ] Copy `resources/js/composables/usePdfPrint.js` from `scholarship-sys`
- [ ] Create `Components/selects/ResponsibilityCenterSelect.vue`
- [ ] Create `Components/selects/ParticularsSelect.vue`

---

## Phase 7 — Access Control

- [ ] Add permissions to the shared DB (via `scholarship-sys` Access Control UI or seeder):
  - `employee_fund_transactions.view`
  - `employee_fund_transactions.manage`
- [ ] Assign to the appropriate staff role(s) in `scholarship-sys`

---

## Phase 8 — Deployment

- [ ] Create virtualhost: `workforce.yakapsaedukasyon.com` → `workforce-portal/public`
- [ ] Confirm both apps have identical `SESSION_COOKIE` value in `.env`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `npm run build`
- [ ] Test: log in on `scholarship-sys` → visit `workforce.yakapsaedukasyon.com` → should be authenticated without re-logging in
- [ ] Test: non-permitted user hitting `/employee-fund-transactions` gets 403
- [ ] Confirm `fund_transactions` table in main app is untouched

---

## Key Reference Files (scholarship-sys)

| File | Purpose |
|------|---------|
| `app/Models/FundTransaction.php` | Model pattern |
| `app/Http/Controllers/Api/FundTransactionController.php` | Controller pattern |
| `resources/js/Pages/FundTransactions/index.vue` | Main page pattern |
| `resources/js/Pages/FundTransactions/Modal/` | Modal patterns |
| `resources/js/Pages/FundTransactions/Pdf/` | PDF template patterns |
| `resources/js/Layouts/AdminLayout.vue` | Layout pattern |
| `resources/css/ios-design-system.css` | Copy as-is |

---

## Coding Standards
Follow the same conventions as `scholarship-sys`:
- `$fillable` — never `$guarded`
- FormRequest for all validation — never inline `$request->validate()`
- `Auth::id()` not `auth()->id()`
- `use Illuminate\Support\Facades\*` — never `\DB::`, `\Log::`
- `DB::transaction()` in services for multi-step ops
- Axios only — never `fetch()` or `XMLHttpRequest`
- PrimeIcons only (`pi pi-*`) — no Heroicons/SVG/FontAwesome
- PrimeVue `<Button>` — never raw `<button>`
- Quill editor for all remarks/notes fields
- `ToggleSwitch` always `size="small"`
- API responses: `{ success: true, message: '...', data: {...} }`
- After modal save: `router.reload({ only: ['prop'] })`
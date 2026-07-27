# ITAssets — OpenCode guide

## Stack

Laravel 13 + Filament 5 + Livewire 4 + Tailwind CSS 4 + Vite.
PHP 8.3+, MySQL in dev, SQLite in-memory in tests.

## Commands

| Action | Command | Notes |
|--------|---------|-------|
| Dev server | `composer run dev` | Starts `php artisan serve`, `php artisan queue:listen --tries=1`, and `npm run dev` via concurrently |
| Run tests | `composer run test` | Runs `config:clear` first, then `php artisan test` |
| Single test | `php artisan test --filter=TestName` | |
| Check expirations | `php artisan notifications:check` | Manually runs the scheduled check |
| Seed DB | `php artisan migrate --seed` | Creates admin user via env vars: `ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD` |

## Testing

- **Pest** (Feature tests use `RefreshDatabase` trait — `tests/Pest.php:17`).
- Test helpers defined in `tests/Pest.php`: `loginAsAdmin()`, `loginAsEditor()`, `loginAsViewer()`, `makeAdminUser()`, etc. They call `createRolesAndPermissions()` which creates roles/permissions programmatically (no DB seed needed).
- `UserFactory` has states: `admin()`, `editor()`, `viewer()` (assign roles after create).
- Tests run with SQLite `:memory:`, queue=sync, cache=array, session=array.

## Architecture

- **Single-panel Filament app** (`app/Providers/Filament/AdminPanelProvider.php`).
- **Resources** (`app/Filament/Resources/`, 10 total): AssetCategories, Assets, Assignments, Departments, Employees, Licenses, Locations, MaintenanceRecords, Suppliers, Users. Nested layout per resource: `Tables/`, `Schemas/`, `Pages/`, `RelationManagers/`.
- **Navigation groups**: declared explicitly via `->navigationGroups(['Inventario', 'Catálogos', 'Administración', 'Sistema'])` on the panel. Inventario = Assets/Assignments/MaintenanceRecords/Licenses/Reports page. Catálogos = AssetCategories/Locations/Suppliers/Departments/Employees. Administración = Users (+ Roles/Permissions from the Spatie roles-permissions plugin). Sistema = GeneralSettings/PdfSettings pages.
- **Permissions**: Spatie `laravel-permission`. Resources use `HasResourcePermissions` trait which overrides Filament's `get{X}AuthorizationResponse()` methods, checking `{action}_{resource}` permissions. Permission names follow: `view_any_{resource}`, `view_{resource}`, `create_{resource}`, `update_{resource}`, `delete_{resource}` + `import_asset`, `export_report`. Roles: Admin (all), Editor (all except delete/import/export), Viewer (view only). New resources need a migration that re-runs `RoleSeeder` in production (deploy pipeline never runs seeders on its own — see `database/migrations/2026_07_24_150000_sync_department_permissions.php`).
- **Users**: `is_active` boolean gates panel access; no hard delete — `EditUser` toggles `is_active` instead (can't deactivate self or the last active Admin). `->passwordReset()` + `->profile()` enabled. Only Admins can change roles.
- **Employees**: `is_active` boolean (not a `status` string); `legajo`/`document_number`/`email`/`position`/`department_id` are all `NOT NULL`; `department_id` is a FK to Departments.
- **Audit**: Spatie `laravel-activitylog` — linked to User via `CausesActivity` + `LogsActivity` traits.
- **Queue**: `database` driver (sync in tests). Worker runs via `composer run dev`.
- **Cache/Session**: `database` driver in dev (array in tests).
- **Scheduler** (`routes/console.php`): `notifications:check` daily at 08:00, timezone read from `Setting::get('timezone', ...)`.

## Key patterns

- **Currency**: `format_currency()` helper in `app/helpers.php` (autoloaded). Formats amounts via `NumberFormatter` (ext-intl). Amounts are stored in the installation's `base_currency` Setting; `display_currency` + `exchange_rate` are optional, applying a conversion only when a secondary reporting currency differs from `base_currency`. `display_locale` controls symbol/number formatting. Configurable via Filament page `GeneralSettings` ("Regional" section), which also holds `timezone` (applied via `FilamentTimezone::set()` in `AdminPanelProvider::boot()`).
- **Setting model**: `App\Models\Setting::get($key, $default)` / `::set($key, $value)`.
- **Notifications**: `WarrantyExpiryNotification`, `LicenseExpiryNotification`, `MaintenanceAlertNotification`, `AssetAssignmentNotification` (`app/Notifications/`). All use the `SendsToManagers` trait, which dual-writes: `$manager->notify($this)` (mail+database) plus `$this->toFilament()->sendToDatabase($manager)` — the second write feeds Filament's in-panel notification bell (`->databaseNotifications()`), which only reads rows with `data->format === 'filament'`. Recipients via `User::scopeManagers()` (Admin+Editor). `CheckExpirations` dedupes repeat sends with a 7-day cooldown.
- **Custom pages** (`app/Filament/Pages/`): `GeneralSettings`, `PdfSettings` (Admin/Editor only via `canAccess()`), `Reports` (export actions, gated per-action). Any page with its own form needs its Blade view wrapped `<x-filament::page><form wire:submit="save">{{ $this->form }}</form></x-filament::page>` — a bare `{{ $this->form }}` has no working submit and no heading.
- **Dashboard widgets** (`app/Filament/Widgets/`): stat cards, 2 charts, warranty/recent-assets/active-maintenance tables, recent-notifications table. A widget query's `->limit(N)` is overridden by Filament's own pagination unless `->paginated(false)` is also set. Widgets lazy-load via `x-intersect` — they only fetch once scrolled into view, which can look like a "blank widget" bug in short-viewport/headless testing but isn't one.
- **PDF**: Assignment PDFs via DomPDF at `GET /assignments/{assignment}/pdf`. `PdfSettings` controls text + an optional `company_logo` (embedded as base64 data URI).
- **Import/Export**: Laravel Excel. `AssetImport` (implements `WithValidation` + `SkipsOnFailure`/`SkipsOnError`), `AssetsExport`, `AssignmentsExport`, `AssetTemplateExport`.
- **Services**: `AssignmentService`, `MaintenanceService` — business logic for assignment/maintenance operations.

## Frameworks

- **Filament resources** use a nested directory layout: `{Resource}/Tables/`, `{Resource}/Schemas/`, `{Resource}/Pages/`, `{Resource}/RelationManagers/`.
- **Editorconfig**: 4-space indent, LF line endings.
- **Vite**: Entry points at `resources/css/app.css` + `resources/js/app.js`. Tailwind CSS 4 via `@tailwindcss/vite` plugin.

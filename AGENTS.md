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
- **Permissions**: Spatie `laravel-permission`. Resources use `HasResourcePermissions` trait which checks `{action}_{resource}` permissions. Permission names follow: `view_any_{resource}`, `view_{resource}`, `create_{resource}`, `update_{resource}`, `delete_{resource}` + `import_asset`, `export_report`.
- **Audit**: Spatie `laravel-activitylog` — linked to User via `CausesActivity` + `LogsActivity` traits.
- **Queue**: `database` driver (sync in tests). Worker runs via `composer run dev`.
- **Cache/Session**: `database` driver in dev (array in tests).
- **Scheduler** (`routes/console.php`): `notifications:check` daily at 08:00.

## Key patterns

- **Currency**: `format_gs()` helper in `app/helpers.php` (autoloaded). Converts USD → Gs. Uses `exchange_rate_usd_pyg` from `Setting` model (key-value store in `settings` table). Configurable via Filament page `GeneralSettings`.
- **Setting model**: `App\Models\Setting::get($key, $default)` / `::set($key, $value)`.
- **Notifications**: `WarrantyExpiryNotification`, `LicenseExpiryNotification`, `MaintenanceAlertNotification`. Sent to Admin + Editor roles.
- **PDF**: Assignment PDFs via DomPDF at `GET /assignments/{assignment}/pdf`.
- **Import/Export**: Laravel Excel. `AssetImport`, `AssetsExport`, `AssignmentsExport`, `AssetTemplateExport`.
- **Services**: `AssignmentService`, `MaintenanceService` — business logic for assignment/maintenance operations.

## Frameworks

- **Filament resources** use a nested directory layout: `{Resource}/Tables/`, `{Resource}/Schemas/`, `{Resource}/Pages/`, `{Resource}/RelationManagers/`.
- **Editorconfig**: 4-space indent, LF line endings.
- **Vite**: Entry points at `resources/css/app.css` + `resources/js/app.js`. Tailwind CSS 4 via `@tailwindcss/vite` plugin.

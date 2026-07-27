# Arquitectura de ITAssets

Este doc es un complemento narrativo al [`CLAUDE.md`](../CLAUDE.md) (que es una referencia rápida de comandos y convenciones) y al [`README.md`](../README.md) (que cubre instalación). Acá el objetivo es otro: explicar el **por qué** detrás de las decisiones de diseño, para que alguien nuevo en el proyecto entienda no solo dónde está cada cosa, sino por qué está ahí.

## El viaje de un request

```
Usuario → Ruta de Filament (/admin/...) → Filament Resource
                                              ├── Schema (form/infolist)
                                              ├── Table
                                              └── Page (List/Create/Edit/View)
                                                    │
                                                    ├── directo al Model (CRUD simple)
                                                    └── vía Service (lógica de negocio con side-effects)
                                                          │
                                                          └── DB (MySQL / SQLite en tests)
```

La mayoría de los 10 resources (`app/Filament/Resources/`) son CRUD simple: el form guarda directo al modelo. Pero cuando una operación tiene reglas de negocio no triviales — como iniciar o cerrar un mantenimiento — esa lógica vive en una capa de Services, no en el Resource. Ver la sección siguiente para el porqué.

## Por qué existe una capa de Services

`app/Services/AssignmentService.php` y `app/Services/MaintenanceService.php` existen porque atar la lógica de negocio directamente a un Filament Resource la deja prisionera de la UI: no se puede reusar desde un comando de consola, un job, o un test unitario sin levantar todo el stack de Livewire.

Ejemplo concreto: cuando se cierra un mantenimiento desde `ViewAsset` (`app/Filament/Resources/Assets/Pages/ViewAsset.php`), `MaintenanceService::close()` no solo marca el registro como completado — también decide a qué estado vuelve el activo (`available`, o lo que el usuario elija). Si esa lógica viviera en el botón de Filament, cualquier otro lugar que necesite "cerrar un mantenimiento" (un comando batch, una importación futura) tendría que duplicarla.

**Cuidado con los side-effects a nivel de modelo.** `MaintenanceRecord` (`app/Models/MaintenanceRecord.php`, método `booted()`) tiene sus propios hooks de Eloquent: crear un registro no completado pone el `Asset` en `maintenance`; editarlo a `completed` lo vuelve a `available`. Estos hooks corren **siempre**, sin importar si el registro se crea desde `MaintenanceService`, desde el CRUD normal de `MaintenanceRecordResource`, o desde un factory en un test. Es una fuente común de sorpresas si no se tiene presente — quedó cubierto explícitamente en `tests/Feature/Filament/MaintenanceRecordResourceTest.php`.

## Cómo funciona el sistema de permisos

Los 10 Filament Resources usan el trait `HasResourcePermissions` (`app/Filament/Concerns/HasResourcePermissions.php`), que traduce cada acción CRUD a un permiso Spatie con el patrón `{action}_{resource}` — por ejemplo `view_any_asset_category`, `delete_maintenance_record`. Los permisos se generan programáticamente (no hay un seeder de producción de permisos suelto — ver la sección siguiente sobre por qué eso importa; en tests, `createRolesAndPermissions()` en `tests/Pest.php` los recrea desde cero) para los 3 roles: **Admin** (todo), **Editor** (todo excepto `delete_*`, `import_asset` y `export_report`), **Viewer** (solo `view_any_*`/`view_*`).

Un detalle importante para quien toque este trait a futuro: Filament no llama a los métodos `canX()` (`canDelete()`, `canCreate()`, etc.) para decidir si mostrar el botón de una acción como "Eliminar" — llama a un método distinto, `getXAuthorizationResponse()` (ej. `getDeleteAuthorizationResponse()`), que por default revisa Policies de Laravel. Como este proyecto no usa Policies, sobreescribir solo `canX()` deja las acciones de la UI sin proteger — hubo un bug real de este tipo (Editor podía borrar registros pese a no tener el permiso) corregido sobreescribiendo los métodos `getXAuthorizationResponse()` correctos. Los métodos `canX()` heredados de Filament ya delegan en ellos automáticamente, así que no hace falta duplicar la lógica.

## Por qué agregar un Resource nuevo requiere una migración de sync de permisos

El pipeline de deploy (`.github/workflows/deploy.yml`, workflow reusable `nextup-py/deploy-actions`) solo corre `php artisan migrate --force` — nunca corre seeders. `RoleSeeder.php` (que genera las filas de permisos y las sincroniza a los 3 roles a partir del array `$resources`) solo se ejecuta manualmente (`php artisan migrate --seed`) o en los tests.

Esto causó un incidente real: al agregar el resource Departamentos (PR #23), el array `$resources` de `RoleSeeder.php` se actualizó correctamente, pero como el deploy nunca re-sembró, la fila de permiso `view_any_department` nunca se creó en producción — y Spatie, al no encontrar el permiso, devuelve `false` silenciosamente (sin error visible) en vez de fallar ruidosamente, así que el resource quedó invisible en el menú para **todos** los usuarios, incluido Admin. El fix (PR #24) fue una migración idempotente que simplemente invoca `(new RoleSeeder())->run()`:

```php
public function up(): void
{
    (new \Database\Seeders\RoleSeeder())->run();
}
```

Como `RoleSeeder::run()` es 100% idempotente (`firstOrCreate` + `syncPermissions()` declarativo), es seguro invocarlo desde una migración — no tiene efecto sobre nada que ya exista, solo crea lo que falta. Ver `database/migrations/2026_07_24_150000_sync_department_permissions.php` como plantilla. **Regla práctica**: cualquier PR que agregue un resource Filament nuevo (y por lo tanto una entrada nueva en `$resources`) debe shippear también una de estas migraciones — de lo contrario el resource queda invisible en producción hasta que alguien corra `migrate --seed` a mano.

## Flujo de notificaciones

```
Disparadores:
  routes/console.php (scheduler, dailyAt 08:00, timezone configurable)
    → notifications:check (CheckExpirations) — warranty/license/maintenance-prolonged
  Model events (siempre, sin importar el punto de entrada UI/servicio):
    → AssignmentAsset::created  → activo asignado
    → Assignment::updated (returned_at) → activo devuelto
    → MaintenanceRecord::created → mantenimiento iniciado

  → por cada destinatario (User::managers(), scope Admin+Editor)
      → $manager->notify(new XxxNotification(...))   // vía SendsToManagers::sendToManager()
            → encolada (ShouldQueue) → tabla `jobs` → queue:listen la procesa → canales `database` + `mail`
      → $notification->toFilament()->sendToDatabase($manager)   // segunda escritura, ver abajo
```

Las 4 notificaciones (`app/Notifications/`: `WarrantyExpiryNotification`, `LicenseExpiryNotification`, `MaintenanceAlertNotification`, `AssetAssignmentNotification`) implementan `ShouldQueue` para no bloquear el comando síncrono del scheduler (ni los model events, que corren dentro del mismo request HTTP) con el envío de emails. El worker de colas corre vía `composer run dev` (`queue:listen --tries=1`, sin reintentos: un fallo va directo a `failed_jobs`).

**Por qué hay un segundo write a `toFilament()->sendToDatabase()`.** El panel de Filament tiene una campana de notificaciones nativa (`->databaseNotifications()` en `AdminPanelProvider.php`), pero esa campana **solo** lee filas de la tabla `notifications` donde `data->format === 'filament'` (`vendor/filament/notifications/src/Livewire/DatabaseNotifications.php`) — un formato que las notificaciones normales de Laravel (`toArray()`/`toMail()`) no producen. La alternativa hubiera sido reescribir las 4 clases para emitir directamente en formato Filament, pero eso hubiera acoplado todo el contenido (asunto de mail, cuerpo, etc.) a la estructura interna de Filament. En cambio, el trait `SendsToManagers` (`app/Notifications/Concerns/SendsToManagers.php`) hace un dual-write: la notificación real de siempre, más un segundo `Filament\Notifications\Notification::make()->sendToDatabase()` solo para alimentar la campana. El costo aceptado: dos filas en `notifications` por cada aviso — se prefirió eso a acoplar el contenido de negocio al formato de UI de un paquete externo.

`CheckExpirations::recentlyNotified()` evita reenviar el mismo aviso todos los días mientras la condición siga siendo verdadera (una garantía vencida sigue "vencida" para siempre): dedupea con una ventana de 7 días, consultando `data->{clave}` (ej. `data->asset_id`) sobre las notificaciones ya enviadas a ese usuario.

## Por qué la moneda es configurable

`format_currency()` (`app/helpers.php`) y el modelo `Setting` (key-value store, `App\Models\Setting::get/set`) existen porque el proyecto empezó pensado para una instalación específica (Paraguay/Guaraníes) y se generalizó después para no asumir un país fijo. `base_currency`, `display_currency`, `exchange_rate` y `display_locale` son configurables desde el panel (`GeneralSettings`), con defaults neutrales (`USD`/`en_US`). Si tocás este helper, cuidado con la aritmética de punto flotante en la conversión — `round()` dos veces (a 6 decimales primero, después a 2) evita que errores de representación binaria redondeen mal montos que caen justo en `.5` centavos.

## El bug del `<form>` en páginas Filament custom

`GeneralSettings` y `PdfSettings` (`app/Filament/Pages/`) son páginas con formulario propio (no un `Resource`/`EditRecord`, que conectan el submit automáticamente vía `content(Schema $schema)`). Durante mucho tiempo sus vistas Blade eran solo `{{ $this->form }}` — y en producción no guardaban ningún cambio ni mostraban heading.

Causa raíz, encontrada comparando con `reports.blade.php` (que sí andaba bien):
- El botón "Guardar cambios" es un `Action::make('save')->submit('save')`, que en HTML es un `<button type="submit">` — **no dispara nada si no tiene un `<form>` ancestro real**. Un `{{ $this->form }}` suelto no envuelve nada en un `<form>`.
- `<x-filament::page>` es el componente que renderiza el heading/breadcrumb estándar del panel — sin él, la página no tiene ninguna de esas piezas de UI.

El fix es siempre el mismo wrapper:
```blade
<x-filament::page>
    <form wire:submit="save">
        {{ $this->form }}
    </form>
</x-filament::page>
```

**Por qué los tests existentes no lo detectaron**: los tests de `GeneralSettingsTest.php`/`PdfSettingsTest.php` usaban `Livewire::test(...)->fillForm([...])->call('save')`, que invoca el método `save()` directamente — sin pasar nunca por el HTML real ni por el click del botón. Por eso se agregó un test a nivel HTTP (`$this->get('/admin/general-settings')->assertSee('wire:submit="save"', false)`) como resguardo permanente: es el único tipo de test que realmente hubiera detectado este bug.

## Por qué `->limit()` en la query de un widget no alcanza para limitar filas

`RecentAssetsWidget` (`app/Filament/Widgets/RecentAssetsWidget.php`) quería mostrar "los últimos 5 activos" con `->query(Asset::latest()->limit(5))`. En producción mostraba hasta 10 (el tamaño de página default de Filament), no 5.

Causa: cuando una tabla de Filament tiene paginación habilitada (el default), `getTableRecords()` no llama `$query->get()` directo — llama a su propio `paginateTableQuery()`, que internamente hace algo equivalente a `$query->forPage($page, $perPage)`. El método `limit()` del query builder de Laravel **reemplaza** el valor de `limit`, no lo combina con uno preexistente — así que el `limit(5)` original queda pisado por el `limit(10)` (o lo que sea `tableRecordsPerPage`) de la paginación.

El fix es agregar `->paginated(false)` al table del widget — eso hace que Filament llame `$query->get()` directamente, sin re-paginar, respetando el `limit()` manual. **Regla práctica**: si un widget necesita mostrar "los últimos N" con N fijo (no configurable por el usuario), siempre necesita `->paginated(false)` además del `->limit(N)` — de lo contrario el límite es puramente cosmético y depende de cuántos registros haya en total.

## Widgets con lazy-loading: por qué "no cargan" en tests headless no es necesariamente un bug

Todos los widgets de Filament heredan `$isLazy = true` por default (`vendor/filament/support/src/Concerns/CanBeLazy.php`). En la práctica esto significa que cada widget se renderiza primero como un placeholder vacío, con un atributo `x-intersect` de Alpine (`$wire.__lazyLoad(...)`) que solo se dispara cuando el placeholder entra al viewport (vía `IntersectionObserver`).

Esto llevó a una investigación larga en una sesión: 3 de 8 widgets del Dashboard aparecían completamente en blanco en un test con Playwright, sin ningún error de servidor. Se descartaron varias hipótesis (duplicación de `recordActions([])`, concurrencia del servidor de desarrollo) antes de confirmar la causa real: esos 3 widgets simplemente estaban más abajo en la página, fuera del viewport chico del navegador headless, así que su `x-intersect` nunca se disparaba — no había ningún bug. Haciendo scroll (o usando un viewport más alto) los 3 widgets cargaban con datos reales normalmente, igual que en un navegador real con un usuario scrolleando la página.

**Lección para el futuro**: si un widget "no carga" en un test automatizado o en una captura de pantalla, confirmar primero con scroll/viewport antes de asumir que hay un bug de backend — es el comportamiento esperado del lazy-loading, no una falla.

## Filosofía de testing

- **Pest**, no PHPUnit — sintaxis `it(...)`/`expect(...)`.
- `RefreshDatabase` en cada test de Feature (`tests/Pest.php`), SQLite en memoria — rápido, sin estado compartido entre tests.
- Los helpers `loginAsAdmin()`/`loginAsEditor()`/`loginAsViewer()` (`tests/Pest.php`) existen para no repetir el setup de roles/permisos en cada archivo — úsalos en vez de crear usuarios y asignar roles a mano.
- Los tests de Filament Resources no se limitan a comprobar que la página carga (`assertOk()`). También verifican que el CRUD via Livewire (`fillForm()`/`call('create')`/`assertHasNoFormErrors()`) realmente persiste datos, que las validaciones de formulario funcionan, y — esto es lo que destapó el bug de permisos mencionado arriba — que Viewer/Editor efectivamente no pueden hacer lo que no deberían (`assertForbidden()`, `assertActionHidden()`, `assertTableBulkActionHidden()`).

## Dónde mirar según lo que quieras cambiar

| Quiero... | Mirar |
|---|---|
| Agregar un campo a un resource | `app/Filament/Resources/{Resource}/Schemas/`, `Tables/` |
| Cambiar una regla de negocio de asignación/mantenimiento | `app/Services/` — no el Resource ni la Page |
| Agregar un permiso o cambiar qué puede hacer un rol | `tests/Pest.php` (`createRolesAndPermissions()`) + `app/Filament/Concerns/HasResourcePermissions.php` |
| Agregar un Resource Filament nuevo | Sumarlo a `$resources` en `database/seeders/RoleSeeder.php` y `tests/Pest.php`, **más** una migración que invoque `(new RoleSeeder())->run()` (ver `2026_07_24_150000_sync_department_permissions.php`) |
| Agregar una notificación nueva | `app/Notifications/` (usar el trait `SendsToManagers` + implementar `toFilament()`), y wiring en un model event o en `app/Console/Commands/CheckExpirations.php` — recordá `implements ShouldQueue` |
| Agregar una página custom con formulario propio | `app/Filament/Pages/`, y su vista en `resources/views/filament/pages/` **debe** usar `<x-filament::page><form wire:submit="save">...</form></x-filament::page>` |
| Agregar/tocar un widget del Dashboard | `app/Filament/Widgets/` — si necesita un `->limit(N)` fijo, sumar también `->paginated(false)` |
| Tocar el formato de moneda | `app/helpers.php` (`format_currency()`) + `App\Models\Setting` |

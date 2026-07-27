# ITAssets

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel)
![Filament](https://img.shields.io/badge/Filament-5-EB8B5E?logo=filament)
![Tests](https://img.shields.io/badge/tests-290_passing-green)

Sistema de gestión de activos informáticos construido con **Laravel 13** + **Filament 5**. Permite administrar el ciclo de vida completo de activos de TI: adquisición, asignación, mantenimiento y baja.

Además del CRUD de activos, incluye: catálogos de Departamentos/Categorías/Ubicaciones/Proveedores, gestión de Empleados y Usuarios (con desactivación en vez de borrado, y autoservicio de contraseña/perfil), notificaciones de vencimiento de garantías/licencias y mantenimientos prolongados (por email y por una campana de notificaciones dentro del panel), Dashboard con estadísticas y gráficos, página de Reportes con exportación a Excel, y Configuración general (moneda, zona horaria) + configuración del PDF de asignación (con logo de empresa).

Para entender el *por qué* detrás de las decisiones de diseño (no solo el *qué*), ver [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

## Stack

- **PHP 8.3** / **Laravel 13**
- **Filament 5** (panel administrador)
- **Livewire 4** / **Tailwind CSS 4** / **Vite**
- **MySQL** (base de datos)
- **Spatie Permissions** (roles y permisos)
- **Spatie Activitylog** (auditoría)
- **DomPDF** (PDF de asignaciones)
- **Laravel Excel** (importación/exportación)

## Recursos gestionados

Activos, Asignaciones, Mantenimientos, Licencias, Categorías de Activos, Ubicaciones, Proveedores, Departamentos, Empleados y Usuarios — organizados en el menú en 4 grupos: **Inventario**, **Catálogos**, **Administración** y **Sistema**.

## Requisitos

- PHP 8.3 o superior
- MySQL / MariaDB
- Composer
- Node.js 18+ y NPM

## Instalación

```bash
# Clonar el repositorio y acceder al directorio
cd itassets

# Instalar dependencias PHP
composer install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Crear base de datos MySQL y configurar .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)

# Ejecutar migraciones y seeders
php artisan migrate --seed

# Compilar assets
npm install
npm run build
```

El seeder crea un usuario administrador por defecto (configurable en `.env`):

| Campo | Default |
|-------|---------|
| ADMIN_NAME | Admin |
| ADMIN_EMAIL | admin@itassets.test |
| ADMIN_PASSWORD | password |

## Roles de usuario

| Rol | Permisos |
|-----|----------|
| **Admin** | Acceso completo a todos los recursos (crear, leer, actualizar, eliminar) |
| **Editor** | CRUD excepto eliminar, importar activos y exportar reportes |
| **Viewer** | Solo lectura en todos los recursos |

## Tests

```bash
php artisan test
```

**290 tests** · **852 assertions** · Cobertura: modelos, servicios, comandos, notificaciones (incluida la campana in-panel), Filament (resources, páginas de configuración/reportes, widgets del Dashboard), importación/exportación.

## Scripts útiles

```bash
# Iniciar entorno de desarrollo (servidor + queue + vite)
composer run dev

# Ejecutar tests
composer run test

# Verificar expiraciones manualmente
php artisan notifications:check
```

## Licencia

MIT

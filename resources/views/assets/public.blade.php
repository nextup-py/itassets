<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $asset->asset_tag }} — {{ $asset->name }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f3f4f6;
            color: #111827;
            margin: 0;
            padding: 24px 16px;
        }
        .card {
            max-width: 420px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
        }
        .tag {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            letter-spacing: 0.05em;
        }
        h1 {
            font-size: 20px;
            margin: 4px 0 0;
        }
        .rows {
            padding: 4px 24px 20px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }
        .row:last-child {
            border-bottom: none;
        }
        .row-label {
            color: #6b7280;
        }
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            background: #e5e7eb;
            color: #374151;
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef9c3; color: #854d0e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-primary { background: #e0e7ff; color: #3730a3; }
        .badge-gray { background: #e5e7eb; color: #374151; }
        .login-cta {
            margin-top: 4px;
            padding: 16px 24px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        .login-cta a {
            display: inline-block;
            padding: 8px 20px;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
        }
        .login-cta p {
            margin: 0 0 10px;
            font-size: 13px;
            color: #6b7280;
        }
    </style>
</head>
<body>

    <div class="card">
        <div class="card-header">
            <div class="tag">{{ $asset->asset_tag }}</div>
            <h1>{{ $asset->name }}</h1>
        </div>

        <div class="rows">
            <div class="row">
                <span class="row-label">Categoría</span>
                <span>{{ $asset->category?->name ?? '—' }}</span>
            </div>

            <div class="row">
                <span class="row-label">Marca / Modelo</span>
                <span>{{ collect([$asset->brand, $asset->model])->filter()->implode(' / ') ?: '—' }}</span>
            </div>

            <div class="row">
                <span class="row-label">N.º de serie</span>
                <span>{{ $asset->serial_number ?? '—' }}</span>
            </div>

            <div class="row">
                <span class="row-label">Estado</span>
                <span class="badge badge-{{ $asset->getStatusBadgeColor() }}">{{ $asset->getStatusLabel() }}</span>
            </div>
        </div>

        <div class="login-cta">
            <p>Iniciá sesión para ver el historial completo, asignación actual y más detalles.</p>
            <a href="{{ \App\Filament\Resources\Assets\AssetResource::getUrl('view', ['record' => $asset]) }}">Iniciar sesión para ver más</a>
        </div>
    </div>

</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Documento de Asignación</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; line-height: 1.5; margin: 30px; color: #222; }
        h1 { text-align: center; font-size: 18px; text-transform: uppercase; border-bottom: 2px solid #222; padding-bottom: 8px; }
        h2 { font-size: 14px; margin-top: 20px; }
        .info-table { width: 100%; margin: 10px 0; }
        .info-table td { padding: 2px 8px; }
        .info-label { font-weight: bold; width: 160px; }
        .clausulas { margin: 15px 0; text-align: justify; }
        .clausulas ol { padding-left: 20px; }
        .clausulas li { margin-bottom: 6px; }
        .asset-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .asset-table th, .asset-table td { border: 1px solid #444; padding: 6px 8px; text-align: left; }
        .asset-table th { background: #e0e0e0; font-size: 11px; }
        .asset-table td { font-size: 11px; }
        .signature { margin-top: 40px; }
        .signature td { padding: 10px 30px; }
        .signature-line { border-top: 1px solid #222; width: 250px; margin: 0 auto; padding-top: 4px; text-align: center; }
        .footer { margin-top: 30px; font-size: 10px; text-align: center; border-top: 1px solid #ccc; padding-top: 8px; }
        .observations { margin: 10px 0; padding: 8px; border: 1px dashed #999; font-style: italic; }
    </style>
</head>
<body>

    @php
        $company = \App\Models\Setting::get('company_name', '');
        $intro   = \App\Models\Setting::get('pdf_intro', '');
        $clauses = \App\Models\Setting::get('pdf_clauses', []);
        $closing = \App\Models\Setting::get('pdf_closing', '');

        $logo = \App\Models\Setting::get('company_logo');
        $logoDataUri = null;
        if ($logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo)) {
            $logoDataUri = 'data:' . \Illuminate\Support\Facades\Storage::disk('public')->mimeType($logo) . ';base64,' . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($logo));
        }
    @endphp

    @if ($logoDataUri)
        <img src="{{ $logoDataUri }}" style="max-height: 60px; display: block; margin: 0 auto 10px;">
    @endif

    <h1>Documento de Asignación de Equipamiento</h1>

    @if ($intro)
        <p>{{ str_replace(
            [':company', ':date', ':employee', ':document', ':position'],
            [$company, $assignment->assigned_at->format('d/m/Y'), optional($assignment->employee)->name ?? '—', optional($assignment->employee)->document_number ?? '—', optional($assignment->employee)->position ?? '—'],
            $intro
        ) }}</p>
    @else
        <p>Documento de asignación de equipamiento a <strong>{{ optional($assignment->employee)->name ?? '—' }}</strong>.</p>
    @endif

    @if (count($clauses))
        <div class="clausulas">
            <ol>
                @foreach ($clauses as $clause)
                    <li>{{ str_replace(':company', $company, $clause) }}</li>
                @endforeach
            </ol>
        </div>
    @endif

    <h2>Detalle del Equipo Entregado</h2>

    <table class="asset-table">
        <thead>
            <tr>
                <th>Marca</th>
                <th>Modelo</th>
                <th>N.º Serie</th>
                <th>Cargador SN</th>
                <th>N.º Ticket</th>
                <th>Fecha Asignación</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($assignment->assets as $asset)
                <tr>
                    <td>{{ $asset->brand ?? '—' }}</td>
                    <td>{{ $asset->model ?? '—' }}</td>
                    <td>{{ $asset->serial_number ?? '—' }}</td>
                    <td>{{ $asset->pivot->charger_serial ?? '—' }}</td>
                    <td>{{ $asset->pivot->ticket_number ?? '—' }}</td>
                    <td>{{ $asset->pivot->assigned_at ? \Carbon\Carbon::parse($asset->pivot->assigned_at)->format('d/m/Y') : '—' }}</td>
                    <td>{{ $asset->pivot->notes ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 4px; font-size: 11px;">
        <strong>Legajo:</strong> {{ optional($assignment->employee)->legajo ?? '—' }}
    </div>

    @if ($assignment->notes)
        <div class="observations">
            <strong>Observaciones generales:</strong><br>
            {{ $assignment->notes }}
        </div>
    @endif

    @if ($closing)
        <p style="margin-top: 16px;">{{ str_replace(':company', $company, $closing) }}</p>
    @endif

    <table class="signature" width="100%">
        <tr>
            <td align="center">
                <div><strong>{{ optional($assignment->employee)->name ?? '—' }}</strong></div>
                <div style="margin-top: 4px; font-size: 11px;">
                    Documento: {{ optional($assignment->employee)->document_number ?? '—' }}
                </div>
                <div style="margin-top: 30px;">
                    <div class="signature-line">Firma</div>
                </div>
            </td>
            <td align="center">
                <div style="margin-top: 30px;">
                    <div class="signature-line">Fecha ___/___/_____</div>
                </div>
            </td>
        </tr>
    </table>

    @if ($assignment->assigned_by)
        <p style="margin-top: 20px; font-size: 10px;">
            Asignado por: <strong>{{ $assignment->assigned_by }}</strong>
        </p>
    @endif

    <div class="footer">
        Documento generado el {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>

<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Assignment;
use App\Models\AssignmentAsset;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AssignmentPdfController extends Controller
{
    public function download(Assignment $assignment): Response
    {
        if (! Auth::user()->can('view_assignment')) {
            throw new AuthorizationException;
        }

        $pdf = Pdf::loadView('pdf.assignment', [
            'assignment' => $assignment->loadMissing('employee', 'assets.category'),
        ]);

        $employee = $assignment->employee;
        $filename = 'asignacion_' . ($employee?->legajo ?? $employee?->id ?? 'sin_empleado') . '_' . $assignment->id . '.pdf';

        return $pdf->stream($filename);
    }

    public function preview(string $token): Response
    {
        if (! (Auth::user()?->hasAnyRole(['Admin', 'Editor']) ?? false)) {
            throw new AuthorizationException;
        }

        $overrides = Cache::get("pdf_preview.{$token}");
        abort_if($overrides === null, 410, 'La vista previa expiró. Volvé a intentarlo.');

        $employee = new Employee([
            'name' => 'Juan Pérez',
            'document_number' => '12.345.678',
            'legajo' => 'EMP-0001',
            'position' => 'Analista de Sistemas',
        ]);

        $asset = new Asset([
            'asset_tag' => 'AST-0001',
            'brand' => 'Dell',
            'model' => 'Latitude 5420',
            'serial_number' => 'SN-EJEMPLO-001',
        ]);
        $asset->setRelation('category', new AssetCategory(['name' => 'Notebook']));
        $asset->setRelation('pivot', new AssignmentAsset([
            'charger_serial' => 'CH-0001',
            'ticket_number' => 'TCK-0001',
            'assigned_at' => now(),
            'notes' => 'Ejemplo de observación.',
        ]));

        $assignment = new Assignment([
            'assigned_at' => now(),
            'assigned_by' => Auth::user()->name,
            'notes' => 'Este es un documento de ejemplo generado desde la vista previa de configuración.',
        ]);
        $assignment->setRelation('employee', $employee);
        $assignment->setRelation('assets', collect([$asset]));

        $pdf = Pdf::loadView('pdf.assignment', [
            'assignment' => $assignment,
            'overrides' => $overrides,
        ]);

        return $pdf->stream('vista_previa.pdf');
    }
}

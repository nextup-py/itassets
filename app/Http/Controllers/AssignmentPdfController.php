<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class AssignmentPdfController extends Controller
{
    public function download(Assignment $assignment): \Illuminate\Http\Response
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
}

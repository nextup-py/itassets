<?php

namespace App\Exports;

use App\Models\Assignment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssignmentsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        public ?string $status = null,
    ) {}

    public function query()
    {
        return Assignment::query()
            ->with(['employee', 'assets'])
            ->when($this->status === 'active', fn ($q) => $q->active())
            ->when($this->status === 'returned', fn ($q) => $q->returned())
            ->orderBy('assigned_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID', 'Empleado', 'Departamento', 'Activos',
            'Asignado por', 'Asignado el', 'Devuelto el', 'Notas',
        ];
    }

    public function map($assignment): array
    {
        return [
            $assignment->id,
            $assignment->employee?->name ?? '—',
            $assignment->employee?->department?->name ?? '—',
            $assignment->assets->map(fn ($a) => "[{$a->asset_tag}] {$a->name}")->implode(', '),
            $assignment->assigned_by ?? '—',
            $assignment->assigned_at?->format(current_date_format()) ?? '—',
            $assignment->returned_at?->format(current_date_format()) ?? 'Activo',
            $assignment->notes ?? '—',
        ];
    }
}

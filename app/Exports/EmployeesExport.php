<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeesExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        public ?int $departmentId = null,
        public ?bool $isActive = null,
    ) {}

    public function query()
    {
        return Employee::query()
            ->with('department')
            ->when($this->departmentId, fn ($q) => $q->where('department_id', $this->departmentId))
            ->when(! is_null($this->isActive), fn ($q) => $q->where('is_active', $this->isActive))
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Legajo', 'Nombre', 'Correo electrónico', 'Teléfono',
            'Departamento', 'Cargo', 'Documento', 'Tipo de documento', 'Activo',
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->legajo,
            $employee->name,
            $employee->email,
            $employee->phone ?? '—',
            $employee->department?->name ?? '—',
            $employee->position,
            $employee->document_number,
            $employee->document_type ? (Employee::DOCUMENT_TYPES[$employee->document_type] ?? $employee->document_type) : '—',
            $employee->is_active ? 'Sí' : 'No',
        ];
    }
}

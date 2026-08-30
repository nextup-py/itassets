<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeeTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                'legajo'           => 'EMP-0001',
                'nombre'           => 'María García',
                'email'            => 'maria.garcia@empresa.test',
                'telefono'         => '+595 981 111 111',
                'departamento'     => 'Sistemas',
                'cargo'            => 'Analista de Soporte',
                'documento'        => '1234567',
                'tipo_documento'   => 'ci',
                'activo'           => 'Sí',
            ],
            [
                'legajo'           => 'EMP-0002',
                'nombre'           => 'Juan Pérez',
                'email'            => 'juan.perez@empresa.test',
                'telefono'         => '+595 981 222 222',
                'departamento'     => 'Administración',
                'cargo'            => 'Contador',
                'documento'        => '7654321',
                'tipo_documento'   => 'ci',
                'activo'           => 'Sí',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'legajo',
            'nombre',
            'email',
            'telefono',
            'departamento',
            'cargo',
            'documento',
            'tipo_documento',
            'activo',
        ];
    }
}

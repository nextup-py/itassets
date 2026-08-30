<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SupplyTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                'nombre'              => 'Mouse inalámbrico',
                'categoria'           => 'Periféricos',
                'cantidad_disponible' => '20',
                'proveedor'           => 'CompuWorld',
                'ubicacion'           => 'Depósito Central',
                'notas'               => '',
            ],
            [
                'nombre'              => 'Cable de red',
                'categoria'           => 'Cables',
                'cantidad_disponible' => '50',
                'proveedor'           => 'Tech Distribuidora',
                'ubicacion'           => 'Depósito Central',
                'notas'               => '',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'nombre',
            'categoria',
            'cantidad_disponible',
            'proveedor',
            'ubicacion',
            'notas',
        ];
    }
}

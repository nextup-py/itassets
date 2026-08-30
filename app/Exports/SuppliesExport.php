<?php

namespace App\Exports;

use App\Models\Supply;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SuppliesExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        public ?int $categoryId = null,
    ) {}

    public function query()
    {
        return Supply::query()
            ->with(['category', 'supplier', 'location'])
            ->when($this->categoryId, fn ($q) => $q->where('asset_category_id', $this->categoryId))
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Nombre', 'Categoría', 'Disponible', 'Dañado / Revisión',
            'Proveedor', 'Ubicación', 'Notas',
        ];
    }

    public function map($supply): array
    {
        return [
            $supply->name,
            $supply->category?->name ?? '—',
            $supply->quantity_available,
            $supply->quantity_damaged,
            $supply->supplier?->name ?? '—',
            $supply->location?->name ?? '—',
            $supply->notes ?? '—',
        ];
    }
}

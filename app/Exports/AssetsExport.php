<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        public ?string $status = null,
        public ?int $categoryId = null,
    ) {}

    public function query()
    {
        return Asset::query()
            ->with(['category', 'supplier', 'location', 'assignments.employee'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->categoryId, fn ($q) => $q->where('asset_category_id', $this->categoryId))
            ->orderBy('asset_tag');
    }

    public function headings(): array
    {
        return [
            'Código', 'Nombre', 'Categoría', 'Marca', 'Modelo',
            'N/S', 'Estado', 'Condición', 'Proveedor', 'Ubicación',
            'Fecha Compra', 'Precio', 'Asignado a',
        ];
    }

    public function map($asset): array
    {
        $assignedTo = $asset->assignments->first(fn ($a) => $a->isActive())?->employee?->name ?? '—';

        return [
            $asset->asset_tag,
            $asset->name,
            $asset->category?->name ?? '—',
            $asset->brand ?? '—',
            $asset->model ?? '—',
            $asset->serial_number ?? '—',
            Asset::STATUSES[$asset->status] ?? $asset->status,
            Asset::CONDITIONS[$asset->condition] ?? $asset->condition ?? '—',
            $asset->supplier?->name ?? '—',
            $asset->location?->name ?? '—',
            $asset->purchase_date?->format('d/m/Y') ?? '—',
            $asset->purchase_price ? \format_gs($asset->purchase_price) : '—',
            $assignedTo,
        ];
    }
}

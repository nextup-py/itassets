<?php

namespace App\Imports;

use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\Supply;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class SupplyImport implements ToModel, WithHeadingRow, WithUpserts, WithValidation, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    protected array $categories = [];
    protected array $suppliers = [];
    protected array $locations = [];

    public function __construct()
    {
        $this->categories = AssetCategory::pluck('id', 'name')->toArray();
        $this->suppliers  = Supplier::pluck('id', 'name')->toArray();
        $this->locations  = Location::pluck('id', 'name')->toArray();
    }

    public function model(array $row): Supply
    {
        $data = [
            'asset_category_id'  => $this->resolveCategory($row['categoria'] ?? null),
            'quantity_available' => (int) ($row['cantidad_disponible'] ?? $row['quantity_available'] ?? 0),
            'supplier_id'        => $this->resolveSupplier($row['proveedor'] ?? null),
            'location_id'        => $this->resolveLocation($row['ubicacion'] ?? null),
            'notes'              => $row['notas'] ?? $row['notes'] ?? null,
        ];

        $name = $row['nombre'] ?? $row['name'] ?? null;

        if ($name) {
            return Supply::updateOrCreate(['name' => $name], $data);
        }

        return Supply::create(['name' => '—', ...$data]);
    }

    public function uniqueBy(): array
    {
        return ['name'];
    }

    public function rules(): array
    {
        return [
            'nombre'              => 'nullable|string|max:255',
            'name'                => 'nullable|string|max:255',
            'categoria'           => 'nullable|string|max:255',
            'cantidad_disponible' => 'nullable|numeric|min:0',
            'quantity_available'  => 'nullable|numeric|min:0',
            'proveedor'           => 'nullable|string|max:255',
            'ubicacion'           => 'nullable|string|max:255',
            'notas'               => 'nullable|string',
        ];
    }

    protected function resolveCategory(?string $name): ?int
    {
        if (empty($name)) return null;

        $name = trim($name);
        if (isset($this->categories[$name])) return $this->categories[$name];

        $category = AssetCategory::firstOrCreate(
            ['name' => $name],
            ['description' => 'Creado automáticamente durante la importación']
        );

        $this->categories[$name] = $category->id;
        return $category->id;
    }

    protected function resolveSupplier(?string $name): ?int
    {
        if (empty($name)) return null;

        $name = trim($name);
        if (isset($this->suppliers[$name])) return $this->suppliers[$name];

        $supplier = Supplier::firstOrCreate(
            ['name' => $name],
            ['contact_name' => null, 'email' => null, 'phone' => null]
        );

        $this->suppliers[$name] = $supplier->id;
        return $supplier->id;
    }

    protected function resolveLocation(?string $name): ?int
    {
        if (empty($name)) return null;

        $name = trim($name);
        if (isset($this->locations[$name])) return $this->locations[$name];

        $location = Location::firstOrCreate(
            ['name' => $name],
            ['building' => null, 'floor' => null, 'room' => null]
        );

        $this->locations[$name] = $location->id;
        return $location->id;
    }
}

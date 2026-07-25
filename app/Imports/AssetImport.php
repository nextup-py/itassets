<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Employee;
use App\Models\Location;
use App\Models\Supplier;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class AssetImport implements ToModel, WithHeadingRow, WithUpserts, WithValidation, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    protected array $categories = [];
    protected array $suppliers = [];
    protected array $locations = [];
    protected array $employees = [];

    public function __construct()
    {
        $this->categories = AssetCategory::pluck('id', 'name')->toArray();
        $this->suppliers  = Supplier::pluck('id', 'name')->toArray();
        $this->locations  = Location::pluck('id', 'name')->toArray();
        $this->employees  = Employee::pluck('id', 'name')->toArray();
    }

    public function model(array $row): Asset
    {
        $categoryId = $this->resolveCategory($row['categoria'] ?? null);
        $supplierId = $this->resolveSupplier($row['proveedor'] ?? null);
        $locationId = $this->resolveLocation($row['ubicacion'] ?? null);

        $status = $this->normalizeStatus($row['estado'] ?? null);
        $assetTag = $row['asset_tag'] ?? $row['codigo'] ?? null;

        $data = [
            'name'              => $row['name'] ?? $row['nombre'] ?? '—',
            'asset_category_id' => $categoryId,
            'brand'             => $row['marca'] ?? null,
            'model'             => $row['modelo'] ?? null,
            'serial_number'     => $row['numero_de_serie'] ?? $row['serial_number'] ?? null,
            'status'            => $status,
            'purchase_date'     => $this->parseDate($row['fecha_de_compra'] ?? $row['purchase_date'] ?? null),
            'purchase_price'    => $this->parseMoney($row['costo'] ?? $row['purchase_price'] ?? null),
            'supplier_id'       => $supplierId,
            'location_id'       => $locationId,
        ];

        if ($assetTag) {
            $asset = Asset::updateOrCreate(['asset_tag' => $assetTag], $data);
        } else {
            $asset = Asset::create($data);
        }

        // Asignación a empleado si se especificó
        $employeeName = $row['empleado_asignado'] ?? $row['employee'] ?? null;
        if ($employeeName) {
            $employeeId = $this->resolveEmployee($employeeName);
            if ($employeeId && ! $asset->assignments()->active()->exists()) {
                $asset->assignments()->create([
                    'employee_id' => $employeeId,
                    'assigned_by' => 'Importación',
                    'assigned_at' => now(),
                ]);
            }
        }

        return $asset;
    }

    public function uniqueBy(): array
    {
        return ['asset_tag'];
    }

    public function rules(): array
    {
        return [
            'asset_tag'         => 'nullable|string|max:255',
            'codigo'            => 'nullable|string|max:255',
            'name'              => 'nullable|string|max:255',
            'nombre'            => 'nullable|string|max:255',
            'marca'             => 'nullable|string|max:255',
            'modelo'            => 'nullable|string|max:255',
            'numero_de_serie'   => 'nullable|string|max:255',
            'serial_number'     => 'nullable|string|max:255',
            'categoria'         => 'nullable|string|max:255',
            'proveedor'         => 'nullable|string|max:255',
            'ubicacion'         => 'nullable|string|max:255',
            'empleado_asignado' => 'nullable|string|max:255',
            'employee'          => 'nullable|string|max:255',
        ];
    }

    // -------------------------------------------------------------------------
    // Resolución de relaciones
    // -------------------------------------------------------------------------
    protected function resolveCategory(?string $name): ?int
    {
        if (empty($name)) return null;

        $name = trim($name);
        if (isset($this->categories[$name])) return $this->categories[$name];

        // Buscar por tipo (case-insensitive)
        $category = AssetCategory::where('name', $name)->first();
        if (! $category) {
            $category = AssetCategory::create([
                'name'        => $name,
                'description' => 'Creado automáticamente durante la importación',
            ]);
        }

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

    protected function resolveEmployee(?string $name): ?int
    {
        if (empty($name)) return null;

        $name = trim($name);
        if (isset($this->employees[$name])) return $this->employees[$name];

        $employee = Employee::firstOrCreate(
            ['name' => $name],
            [
                'status' => 'active',
                'legajo' => 'IMPORT-' . strtoupper(Str::random(6)),
                'document_number' => 'IMPORT-' . strtoupper(Str::random(6)),
                'department_id' => null,
                'position' => null,
            ]
        );

        $this->employees[$name] = $employee->id;
        return $employee->id;
    }

    // -------------------------------------------------------------------------
    // Utilidades
    // -------------------------------------------------------------------------
    protected function normalizeStatus(?string $status): string
    {
        if (empty($status)) return 'stock';

        $map = [
            'disponible'       => 'available',
            'available'        => 'available',
            'asignado'         => 'assigned',
            'asignada'         => 'assigned',
            'asignado / en uso'=> 'assigned',
            'en uso'           => 'assigned',
            'en mantenimiento' => 'maintenance',
            'mantenimiento'    => 'maintenance',
            'dado de baja'     => 'retired',
            'baja'             => 'retired',
            'perdido'          => 'lost',
            'perdido/robado'   => 'lost',
            'robado'           => 'lost',
            'en stock'         => 'stock',
            'stock'            => 'stock',
            'almacén'          => 'stock',
        ];

        $key = mb_strtolower(trim($status));
        return $map[$key] ?? 'stock';
    }

    protected function parseDate($value): ?string
    {
        if (empty($value)) return null;

        // If it's a numeric Excel serial date
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                ?->format('Y-m-d');
        }

        // If it's a string like "15/01/2024" or "2024-01-15"
        $date = str_replace(['/', '-'], '-', $value);

        // Try multiple formats
        $formats = ['Y-m-d', 'd-m-Y', 'Y/m/d', 'm/d/Y'];
        foreach ($formats as $format) {
            $parsed = \DateTime::createFromFormat($format, $date);
            if ($parsed) return $parsed->format('Y-m-d');
        }

        return null;
    }

    protected function parseMoney($value): ?float
    {
        if (empty($value)) return null;
        if (is_numeric($value)) return (float) $value;

        // Remove currency symbols and separators
        $clean = preg_replace('/[^0-9.,\-]/', '', (string) $value);
        $clean = str_replace(',', '', $clean);

        return is_numeric($clean) ? (float) $clean : null;
    }
}

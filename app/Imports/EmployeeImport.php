<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmployeeImport implements ToModel, WithHeadingRow, WithUpserts, WithValidation, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    protected array $departments = [];

    public function __construct()
    {
        $this->departments = Department::pluck('id', 'name')->toArray();
    }

    public function model(array $row): Employee
    {
        $departmentId = $this->resolveDepartment($row['departamento'] ?? null);

        $data = [
            'name'            => $row['nombre'] ?? $row['name'] ?? '—',
            'email'           => $row['email'] ?? $row['correo_electronico'] ?? 'import-' . strtolower(Str::random(8)) . '@pendiente.itassets.test',
            'phone'           => $row['telefono'] ?? $row['phone'] ?? null,
            'department_id'   => $departmentId,
            'position'        => $row['cargo'] ?? $row['position'] ?? 'Pendiente',
            'document_number' => isset($row['documento']) || isset($row['document_number'])
                ? (string) ($row['documento'] ?? $row['document_number'])
                : 'IMPORT-' . strtoupper(Str::random(6)),
            'document_type'   => $row['tipo_documento'] ?? $row['document_type'] ?? null,
            'is_active'       => $this->normalizeActive($row['activo'] ?? $row['is_active'] ?? null),
        ];

        $legajo = isset($row['legajo']) ? (string) $row['legajo'] : null;

        if ($legajo) {
            return Employee::updateOrCreate(['legajo' => $legajo], $data);
        }

        return Employee::create(['legajo' => 'IMPORT-' . strtoupper(Str::random(6)), ...$data]);
    }

    public function uniqueBy(): array
    {
        return ['legajo'];
    }

    public function rules(): array
    {
        return [
            'legajo'         => 'nullable|max:255',
            'nombre'         => 'nullable|string|max:255',
            'name'           => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'telefono'       => 'nullable|max:255',
            'departamento'   => 'nullable|string|max:255',
            'cargo'          => 'nullable|string|max:255',
            'documento'      => 'nullable|max:255',
            'tipo_documento' => 'nullable|string|max:255',
            'activo'         => 'nullable|max:50',
        ];
    }

    protected function resolveDepartment(?string $name): ?int
    {
        if (empty($name)) return $this->resolveDefaultDepartmentId();

        $name = trim($name);
        if (isset($this->departments[$name])) return $this->departments[$name];

        $department = Department::firstOrCreate(['name' => $name]);

        $this->departments[$name] = $department->id;
        return $department->id;
    }

    protected function resolveDefaultDepartmentId(): int
    {
        if (! isset($this->departments['Sin asignar'])) {
            $this->departments['Sin asignar'] = Department::firstOrCreate(['name' => 'Sin asignar'])->id;
        }

        return $this->departments['Sin asignar'];
    }

    protected function normalizeActive($value): bool
    {
        if (is_null($value)) return true;
        if (is_bool($value)) return $value;

        $key = mb_strtolower(trim((string) $value));

        return ! in_array($key, ['no', 'false', '0', 'inactivo'], true);
    }
}

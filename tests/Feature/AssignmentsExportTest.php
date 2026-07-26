<?php

use App\Exports\AssignmentsExport;
use App\Models\Asset;
use App\Models\Assignment;
use App\Models\Employee;

beforeEach(function () {
    loginAsAdmin();
});

it('exports assignments with headings', function () {
    $export = new AssignmentsExport;
    $headings = $export->headings();

    expect($headings)->toBe([
        'ID', 'Empleado', 'Departamento', 'Activos',
        'Asignado por', 'Asignado el', 'Devuelto el', 'Notas',
    ]);
});

it('filters assignments by active status', function () {
    Assignment::factory()->create();
    Assignment::factory()->returned()->create();

    $export = new AssignmentsExport(status: 'active');
    $results = $export->query()->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->returned_at)->toBeNull();
});

it('filters assignments by returned status', function () {
    Assignment::factory()->create();
    Assignment::factory()->returned()->create();

    $export = new AssignmentsExport(status: 'returned');
    $results = $export->query()->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->returned_at)->not->toBeNull();
});

it('maps assignment data correctly', function () {
    $employee = Employee::factory()->create(['name' => 'Ana Pérez']);
    $asset = Asset::factory()->create(['asset_tag' => 'IT-0042', 'name' => 'Laptop Test']);

    $assignment = Assignment::factory()->create([
        'employee_id' => $employee->id,
        'assigned_by' => 'Admin',
        'assigned_at' => '2026-01-15',
        'notes' => 'Nota de prueba',
    ]);
    $assignment->assets()->attach($asset->id, ['assigned_at' => $assignment->assigned_at]);

    $export = new AssignmentsExport;
    $mapped = $export->map($assignment->fresh(['employee', 'assets']));

    expect($mapped[0])->toBe($assignment->id);
    expect($mapped[1])->toBe('Ana Pérez');
    expect($mapped[2])->toBe($employee->department->name);
    expect($mapped[3])->toBe('[IT-0042] Laptop Test');
    expect($mapped[4])->toBe('Admin');
    expect($mapped[5])->toBe('15/01/2026');
    expect($mapped[6])->toBe('Activo');
    expect($mapped[7])->toBe('Nota de prueba');
});

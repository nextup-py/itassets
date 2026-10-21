<?php

use App\Models\Assignment;
use App\Models\Asset;
use App\Models\Department;
use App\Models\Employee;

beforeEach(function () {
    loginAsAdmin();
});

it('has active scope', function () {
    Assignment::factory()->count(2)->create();
    Assignment::factory()->returned()->create();

    expect(Assignment::active()->count())->toBe(2);
});

it('has returned scope', function () {
    Assignment::factory()->count(2)->create();
    Assignment::factory()->returned()->count(3)->create();

    expect(Assignment::returned()->count())->toBe(3);
});

it('belongs to an employee', function () {
    $employee = Employee::factory()->create();
    $assignment = Assignment::factory()->create(['employee_id' => $employee->id]);

    expect($assignment->employee)->toBeInstanceOf(Employee::class);
    expect($assignment->employee->id)->toBe($employee->id);
});

it('can attach assets', function () {
    $assignment = Assignment::factory()->create();
    $assets = Asset::factory()->count(2)->create();

    $assignment->assets()->attach($assets->pluck('id')->toArray(), [
        'assigned_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect($assignment->assets()->count())->toBe(2);
});

it('belongs to a department', function () {
    $department = Department::factory()->create();
    $assignment = Assignment::factory()->toDepartment()->create(['department_id' => $department->id]);

    expect($assignment->department)->toBeInstanceOf(Department::class);
    expect($assignment->department->id)->toBe($department->id);
    expect($assignment->employee_id)->toBeNull();
});

it('rejects an assignment with neither employee nor department', function () {
    expect(fn () => Assignment::factory()->create(['employee_id' => null, 'department_id' => null]))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects an assignment with both employee and department', function () {
    $employee = Employee::factory()->create();
    $department = Department::factory()->create();

    expect(fn () => Assignment::factory()->create(['employee_id' => $employee->id, 'department_id' => $department->id]))
        ->toThrow(InvalidArgumentException::class);
});

it('labels the recipient by employee or department', function () {
    $employeeAssignment = Assignment::factory()->create(['employee_id' => Employee::factory()->create(['name' => 'Ana Pérez'])->id]);
    $departmentAssignment = Assignment::factory()->toDepartment()->create(['department_id' => Department::factory()->create(['name' => 'Soporte'])->id]);

    expect($employeeAssignment->getRecipientLabel())->toBe('Ana Pérez');
    expect($departmentAssignment->getRecipientLabel())->toBe('Soporte');
});

it('resolves the recipient employee via the department contact when unassigned to a person', function () {
    $contact = Employee::factory()->create(['name' => 'Contacto Depto']);
    $department = Department::factory()->create(['contact_employee_id' => $contact->id]);
    $assignment = Assignment::factory()->toDepartment()->create(['department_id' => $department->id]);

    expect($assignment->recipientEmployee()->id)->toBe($contact->id);
});

it('normalizes recipient data based on recipient_type', function () {
    $employee = Employee::factory()->create();
    $department = Department::factory()->create();

    $employeeData = Assignment::normalizeRecipientData([
        'recipient_type' => 'employee',
        'employee_id' => $employee->id,
        'department_id' => $department->id,
    ]);
    expect($employeeData)->toBe(['employee_id' => $employee->id, 'department_id' => null]);

    $departmentData = Assignment::normalizeRecipientData([
        'recipient_type' => 'department',
        'employee_id' => $employee->id,
        'department_id' => $department->id,
    ]);
    expect($departmentData)->toBe(['employee_id' => null, 'department_id' => $department->id]);
});

it('marks assets as available when returned', function () {
    $asset = Asset::factory()->available()->create();
    $assignment = Assignment::factory()->create();

    $assignment->assets()->attach($asset->id, [
        'assigned_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    expect($asset->fresh()->status)->toBe('assigned');

    $assignment->update(['returned_at' => now()]);
    expect($asset->fresh()->status)->toBe('available');
});

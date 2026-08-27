<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\Supply;
use App\Services\SupplyMovementService;

it('decreases only the delivered supply stock on entrega', function () {
    $supply = Supply::factory()->create(['quantity_available' => 10]);
    $employee = Employee::factory()->create();

    app(SupplyMovementService::class)->register($supply, [
        'type' => 'entrega',
        'quantity' => 3,
        'employee_id' => $employee->id,
        'performed_at' => now(),
    ]);

    expect($supply->fresh())
        ->quantity_available->toBe(7)
        ->quantity_damaged->toBe(0);
});

it('decreases delivered stock and increases the same supply damaged bucket on reemplazo with no target given', function () {
    $supply = Supply::factory()->create(['quantity_available' => 10, 'quantity_damaged' => 0]);
    $department = Department::factory()->create();

    app(SupplyMovementService::class)->register($supply, [
        'type' => 'reemplazo',
        'quantity' => 4,
        'department_id' => $department->id,
        'performed_at' => now(),
    ]);

    expect($supply->fresh())
        ->quantity_available->toBe(6)
        ->quantity_damaged->toBe(4);
});

it('increases a different received supply on reemplazo when one is given', function () {
    $delivered = Supply::factory()->create(['quantity_available' => 10]);
    $received = Supply::factory()->create(['quantity_damaged' => 0]);
    $employee = Employee::factory()->create();

    app(SupplyMovementService::class)->register($delivered, [
        'type' => 'reemplazo',
        'quantity' => 2,
        'employee_id' => $employee->id,
        'received_supply_id' => $received->id,
        'performed_at' => now(),
    ]);

    expect($delivered->fresh()->quantity_available)->toBe(8)
        ->and($received->fresh()->quantity_damaged)->toBe(2);
});

it('refuses to register a movement for more than the available stock', function () {
    $supply = Supply::factory()->create(['quantity_available' => 2]);
    $employee = Employee::factory()->create();

    app(SupplyMovementService::class)->register($supply, [
        'type' => 'entrega',
        'quantity' => 5,
        'employee_id' => $employee->id,
        'performed_at' => now(),
    ]);
})->throws(RuntimeException::class);

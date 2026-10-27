<?php

use App\Models\Asset;
use App\Models\MaintenanceRecord;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    loginAsAdmin();
});

it('does not move the asset to maintenance when created as pending', function () {
    $asset = Asset::factory()->available()->create();

    MaintenanceRecord::factory()->for($asset)->create(['status' => 'pending']);

    expect($asset->fresh()->status)->toBe('available');
});

it('moves the asset to maintenance when created as in_progress', function () {
    $asset = Asset::factory()->available()->create();

    MaintenanceRecord::factory()->for($asset)->create(['status' => 'in_progress']);

    expect($asset->fresh()->status)->toBe('maintenance');
});

it('moves the asset to maintenance when a pending record transitions to in_progress', function () {
    $asset = Asset::factory()->available()->create();
    $record = MaintenanceRecord::factory()->for($asset)->create(['status' => 'pending']);

    expect($asset->fresh()->status)->toBe('available');

    $record->update(['status' => 'in_progress']);

    expect($asset->fresh()->status)->toBe('maintenance');
});

it('requires at least one of technician or supplier_id', function () {
    $asset = Asset::factory()->available()->create();

    expect(fn () => MaintenanceRecord::factory()->for($asset)->create([
        'technician' => null,
        'supplier_id' => null,
    ]))->toThrow(ValidationException::class);
});

it('allows a maintenance record with only a technician', function () {
    $asset = Asset::factory()->available()->create();

    $record = MaintenanceRecord::factory()->for($asset)->create([
        'technician' => 'John Tech',
        'supplier_id' => null,
    ]);

    expect($record->exists)->toBeTrue();
});

it('allows a maintenance record with only a supplier', function () {
    $asset = Asset::factory()->available()->create();
    $supplier = \App\Models\Supplier::factory()->create();

    $record = MaintenanceRecord::factory()->for($asset)->create([
        'technician' => null,
        'supplier_id' => $supplier->id,
    ]);

    expect($record->exists)->toBeTrue();
});

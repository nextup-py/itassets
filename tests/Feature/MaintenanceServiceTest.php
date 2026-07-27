<?php

use App\Models\Asset;
use App\Models\MaintenanceRecord;
use App\Models\Supplier;
use App\Services\MaintenanceService;

beforeEach(function () {
    $this->admin = loginAsAdmin();
    $this->service = app(MaintenanceService::class);
});

it('starts a maintenance record', function () {
    $asset = Asset::factory()->available()->create();

    $record = $this->service->start($asset, [
        'type'        => 'repair',
        'description' => 'Broken screen',
        'technician'  => 'John Tech',
        'started_at'  => '2026-07-01',
    ]);

    expect($record)->toBeInstanceOf(MaintenanceRecord::class);
    expect($record->asset_id)->toBe($asset->id);
    expect($record->type)->toBe('repair');
    expect($record->status)->toBe('in_progress');
    expect($record->description)->toBe('Broken screen');
    expect($record->technician)->toBe('John Tech');
});

it('starts maintenance with supplier', function () {
    $asset = Asset::factory()->available()->create();
    $supplier = Supplier::factory()->create();

    $record = $this->service->start($asset, [
        'type'        => 'preventive',
        'description' => 'Annual checkup',
        'supplier_id' => $supplier->id,
        'started_at'  => '2026-07-01',
    ]);

    expect($record->supplier_id)->toBe($supplier->id);
});

it('closes a maintenance record', function () {
    $asset = Asset::factory()->create();
    $record = $this->service->start($asset, [
        'type'        => 'repair',
        'description' => 'Fix keyboard',
        'started_at'  => '2026-06-01',
    ]);

    $this->service->close($asset, [
        'completed_at'    => '2026-06-15',
        'resolution'      => 'Keyboard replaced',
        'new_asset_status' => 'available',
    ]);

    $record->refresh();
    expect($record->status)->toBe('completed');
    expect($record->completed_at->format('Y-m-d'))->toBe('2026-06-15');
    expect($record->resolution)->toBe('Keyboard replaced');
    expect($asset->fresh()->status)->toBe('available');
});

it('returns suppliers list', function () {
    Supplier::factory()->count(3)->create();

    $suppliers = $this->service->getSuppliers();

    expect($suppliers)->toHaveCount(3);
});

it('notifies managers when a new maintenance record is started', function () {
    $asset = Asset::factory()->available()->create();

    $record = $this->service->start($asset, [
        'type'        => 'repair',
        'description' => 'Broken screen',
        'started_at'  => '2026-07-01',
    ]);

    $notification = $this->admin->notifications()
        ->where('type', \App\Notifications\MaintenanceAlertNotification::class)
        ->where('data->alert_type', 'started')
        ->first();

    expect($notification)->not->toBeNull();
    expect($notification->data['maintenance_id'])->toBe($record->id);

    $filamentNotification = $this->admin->notifications()
        ->where('type', \Filament\Notifications\DatabaseNotification::class)
        ->where('data->format', 'filament')
        ->where('data->title', 'like', 'Mantenimiento iniciado%')
        ->first();

    expect($filamentNotification)->not->toBeNull();
});

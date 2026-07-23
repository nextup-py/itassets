<?php

use App\Filament\Resources\MaintenanceRecords\Pages\CreateMaintenanceRecord;
use App\Filament\Resources\MaintenanceRecords\Pages\EditMaintenanceRecord;
use App\Filament\Resources\MaintenanceRecords\Pages\ListMaintenanceRecords;
use App\Models\Asset;
use App\Models\MaintenanceRecord;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('lists maintenance records', function () {
    MaintenanceRecord::factory()->count(3)->create();

    $this->get('/admin/maintenance-records')->assertOk();
});

it('creates a maintenance record', function () {
    $asset = Asset::factory()->available()->create();

    Livewire::test(CreateMaintenanceRecord::class)
        ->fillForm([
            'asset_id' => $asset->id,
            'type' => 'repair',
            'status' => 'pending',
            'description' => 'Falla de teclado',
            'started_at' => now()->toDateString(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(MaintenanceRecord::where('asset_id', $asset->id)->exists())->toBeTrue();
});

it('requires description, type, status, asset and started_at', function () {
    Livewire::test(CreateMaintenanceRecord::class)
        ->fillForm(['description' => ''])
        ->call('create')
        ->assertHasFormErrors(['asset_id', 'type', 'description']);
});

it('rejects a completed_at before started_at', function () {
    $asset = Asset::factory()->available()->create();

    Livewire::test(CreateMaintenanceRecord::class)
        ->fillForm([
            'asset_id' => $asset->id,
            'type' => 'repair',
            'status' => 'in_progress',
            'description' => 'Falla de teclado',
            'started_at' => now()->toDateString(),
            'completed_at' => now()->subDay()->toDateString(),
        ])
        ->call('create')
        ->assertHasFormErrors(['completed_at']);
});

it('edits a maintenance record', function () {
    $record = MaintenanceRecord::factory()->inProgress()->create();

    Livewire::test(EditMaintenanceRecord::class, ['record' => $record->getRouteKey()])
        ->fillForm(['description' => 'Updated description'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->fresh()->description)->toBe('Updated description');
});

it('returns 404 for a non-existent maintenance record', function () {
    $this->get('/admin/maintenance-records/99999')->assertNotFound();
});

it('sets the related asset to maintenance when a non-completed record is created', function () {
    $asset = Asset::factory()->available()->create();

    Livewire::test(CreateMaintenanceRecord::class)
        ->fillForm([
            'asset_id' => $asset->id,
            'type' => 'repair',
            'status' => 'in_progress',
            'description' => 'Falla de teclado',
            'started_at' => now()->toDateString(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect($asset->fresh()->status)->toBe('maintenance');
});

it('sets the related asset back to available when a record is edited to completed', function () {
    $asset = Asset::factory()->maintenance()->create();
    $record = MaintenanceRecord::factory()->inProgress()->for($asset)->create();

    Livewire::test(EditMaintenanceRecord::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'status' => 'completed',
            'completed_at' => now()->toDateString(),
            'resolution' => 'Teclado reemplazado',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($asset->fresh()->status)->toBe('available');
});

it('denies viewer from creating a maintenance record', function () {
    loginAsViewer();

    Livewire::test(CreateMaintenanceRecord::class)->assertForbidden();
});

it('denies viewer from editing a maintenance record', function () {
    $record = MaintenanceRecord::factory()->create();
    loginAsViewer();

    Livewire::test(EditMaintenanceRecord::class, ['record' => $record->getRouteKey()])->assertForbidden();
});

it('hides the delete action from editor on the edit page', function () {
    $record = MaintenanceRecord::factory()->create();
    loginAsEditor();

    Livewire::test(EditMaintenanceRecord::class, ['record' => $record->getRouteKey()])
        ->assertActionHidden('delete');
});

it('hides the delete bulk action from editor on the list page', function () {
    MaintenanceRecord::factory()->create();
    loginAsEditor();

    Livewire::test(ListMaintenanceRecords::class)->assertTableBulkActionHidden('delete');
});

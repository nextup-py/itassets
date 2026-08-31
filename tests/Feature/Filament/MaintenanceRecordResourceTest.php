<?php

use App\Filament\Resources\MaintenanceRecords\Pages\CreateMaintenanceRecord;
use App\Filament\Resources\MaintenanceRecords\Pages\EditMaintenanceRecord;
use App\Filament\Resources\MaintenanceRecords\Pages\ListMaintenanceRecords;
use App\Filament\Resources\MaintenanceRecords\Pages\ViewMaintenanceRecord;
use App\Models\Asset;
use App\Models\MaintenanceRecord;
use App\Models\Setting;
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
            'new_asset_status' => 'available',
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

it('formats the cost column using the configured currency instead of a hardcoded one', function () {
    Setting::set('base_currency', 'EUR');
    Setting::set('display_locale', 'de_DE');
    MaintenanceRecord::factory()->create(['cost' => 100]);

    Livewire::test(ListMaintenanceRecords::class)
        ->toggleAllTableColumns()
        ->assertSee('100,00 €');
});

it('reverts the asset to available when its only active maintenance record is deleted', function () {
    $asset = Asset::factory()->maintenance()->create();
    $record = MaintenanceRecord::factory()->inProgress()->for($asset)->create();

    $record->delete();

    expect($asset->fresh()->status)->toBe('available');
});

it('does not revert the asset when another active maintenance record remains', function () {
    $asset = Asset::factory()->maintenance()->create();
    $record = MaintenanceRecord::factory()->inProgress()->for($asset)->create();
    MaintenanceRecord::factory()->inProgress()->for($asset)->create();

    $record->delete();

    expect($asset->fresh()->status)->toBe('maintenance');
});

it('lets the user choose the resulting asset status when completing a maintenance record', function () {
    $asset = Asset::factory()->maintenance()->create();
    $record = MaintenanceRecord::factory()->inProgress()->for($asset)->create();

    Livewire::test(EditMaintenanceRecord::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'status' => 'completed',
            'new_asset_status' => 'retired',
            'completed_at' => now()->toDateString(),
            'resolution' => 'Equipo dado de baja',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($asset->fresh()->status)->toBe('retired');
});

it('filters prolonged maintenance records (more than 7 days old, still open)', function () {
    $prolonged = MaintenanceRecord::factory()->inProgress()->create(['started_at' => now()->subDays(10)]);
    $recent = MaintenanceRecord::factory()->inProgress()->create(['started_at' => now()->subDays(2)]);
    $completed = MaintenanceRecord::factory()->completed()->create(['started_at' => now()->subDays(10)]);

    Livewire::test(ListMaintenanceRecords::class)
        ->filterTable('prolonged')
        ->assertCanSeeTableRecords([$prolonged])
        ->assertCanNotSeeTableRecords([$recent, $completed]);
});

it('marks a maintenance record as completed from the view page header action', function () {
    $record = MaintenanceRecord::factory()->inProgress()->create();

    Livewire::test(ViewMaintenanceRecord::class, ['record' => $record->getRouteKey()])
        ->callAction('complete')
        ->assertNotified();

    expect($record->refresh()->status)->toBe('completed')
        ->and($record->completed_at)->not->toBeNull();
});

it('hides the complete header action on an already-completed maintenance record', function () {
    $record = MaintenanceRecord::factory()->completed()->create();

    Livewire::test(ViewMaintenanceRecord::class, ['record' => $record->getRouteKey()])
        ->assertActionHidden('complete');
});

it('ignores changes to cost, technician and dates on an already-completed maintenance record', function () {
    $record = MaintenanceRecord::factory()->completed()->create([
        'cost' => 100,
        'technician' => 'Juan Pérez',
        'started_at' => now()->subDays(5),
        'completed_at' => now()->subDay(),
    ]);

    Livewire::test(EditMaintenanceRecord::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'cost' => 999,
            'technician' => 'Otro Técnico',
            'new_asset_status' => 'available',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->fresh()->cost)->toEqual(100)
        ->and($record->fresh()->technician)->toBe('Juan Pérez');
});

it('shows asset tag and type, not just the type, as the maintenance record page title', function () {
    $record = MaintenanceRecord::factory()->create(['type' => 'preventive']);

    Livewire::test(ViewMaintenanceRecord::class, ['record' => $record->getRouteKey()])
        ->assertSee($record->asset->asset_tag . ' - ' . $record->getTypeLabel());
});

<?php

use App\Filament\Resources\Assets\Pages\ViewAsset;
use App\Filament\Resources\Assets\RelationManagers\MaintenanceRecordsRelationManager;
use App\Filament\Resources\MaintenanceRecords\MaintenanceRecordResource;
use App\Models\Asset;
use App\Models\MaintenanceRecord;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('links each row to the maintenance record\'s own View page instead of editing inline', function () {
    $asset = Asset::factory()->create();
    $record = MaintenanceRecord::factory()->inProgress()->for($asset)->create();

    Livewire::test(MaintenanceRecordsRelationManager::class, [
        'ownerRecord' => $asset,
        'pageClass' => ViewAsset::class,
    ])
        ->assertTableActionHasUrl('view', MaintenanceRecordResource::getUrl('view', ['record' => $record]), $record);
});

it('applies the chosen new_asset_status when creating an already-completed record from this tab', function () {
    $asset = Asset::factory()->available()->create();

    Livewire::test(MaintenanceRecordsRelationManager::class, [
        'ownerRecord' => $asset,
        'pageClass' => ViewAsset::class,
    ])
        ->mountTableAction('create')
        ->setTableActionData([
            'type' => 'repair',
            'status' => 'completed',
            'new_asset_status' => 'retired',
            'description' => 'Falla de teclado',
            'started_at' => now()->subDay()->toDateString(),
            'completed_at' => now()->toDateString(),
        ])
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    expect($asset->fresh()->status)->toBe('retired');
});

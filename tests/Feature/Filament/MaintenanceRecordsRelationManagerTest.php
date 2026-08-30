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

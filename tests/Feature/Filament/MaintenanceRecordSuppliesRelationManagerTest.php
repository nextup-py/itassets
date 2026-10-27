<?php

use App\Filament\Resources\MaintenanceRecords\Pages\ViewMaintenanceRecord;
use App\Filament\Resources\MaintenanceRecords\RelationManagers\SuppliesRelationManager;
use App\Models\MaintenanceRecord;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('shows attach/detach actions on an open maintenance record', function () {
    $record = MaintenanceRecord::factory()->inProgress()->create();

    Livewire::test(SuppliesRelationManager::class, [
        'ownerRecord' => $record,
        'pageClass' => ViewMaintenanceRecord::class,
    ])
        ->assertTableActionVisible('attach');
});

it('hides attach/detach actions on an already-completed maintenance record', function () {
    $record = MaintenanceRecord::factory()->completed()->create();

    Livewire::test(SuppliesRelationManager::class, [
        'ownerRecord' => $record,
        'pageClass' => ViewMaintenanceRecord::class,
    ])
        ->assertTableActionHidden('attach');
});

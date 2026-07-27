<?php

use App\Filament\Widgets\ActiveMaintenanceWidget;
use App\Models\Asset;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('shows assets currently in maintenance', function () {
    $asset = Asset::factory()->maintenance()->create();

    Livewire::test(ActiveMaintenanceWidget::class)
        ->assertCanSeeTableRecords([$asset]);
});

it('does not show assets that are not in maintenance', function () {
    $asset = Asset::factory()->available()->create();

    Livewire::test(ActiveMaintenanceWidget::class)
        ->assertCanNotSeeTableRecords([$asset]);
});

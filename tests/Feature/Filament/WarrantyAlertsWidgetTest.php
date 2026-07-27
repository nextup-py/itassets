<?php

use App\Filament\Widgets\WarrantyAlertsWidget;
use App\Models\Asset;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('shows assets with a warranty expiring within the next 60 days', function () {
    $asset = Asset::factory()->create(['warranty_expiry_date' => now()->addDays(30)]);

    Livewire::test(WarrantyAlertsWidget::class)
        ->assertCanSeeTableRecords([$asset]);
});

it('shows assets whose warranty expired within the last 60 days', function () {
    $asset = Asset::factory()->create(['warranty_expiry_date' => now()->subDays(30)]);

    Livewire::test(WarrantyAlertsWidget::class)
        ->assertCanSeeTableRecords([$asset]);
});

it('does not show assets whose warranty expired more than 60 days ago', function () {
    $asset = Asset::factory()->create(['warranty_expiry_date' => now()->subDays(90)]);

    Livewire::test(WarrantyAlertsWidget::class)
        ->assertCanNotSeeTableRecords([$asset]);
});

it('does not show assets whose warranty expires more than 60 days from now', function () {
    $asset = Asset::factory()->create(['warranty_expiry_date' => now()->addDays(90)]);

    Livewire::test(WarrantyAlertsWidget::class)
        ->assertCanNotSeeTableRecords([$asset]);
});

it('does not show assets without a warranty expiry date', function () {
    $asset = Asset::factory()->create(['warranty_expiry_date' => null]);

    Livewire::test(WarrantyAlertsWidget::class)
        ->assertCanNotSeeTableRecords([$asset]);
});

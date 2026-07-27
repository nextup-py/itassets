<?php

use App\Filament\Widgets\AssetValueWidget;
use App\Models\Asset;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('shows the total inventory value and average price', function () {
    Asset::factory()->create(['purchase_price' => 1000]);
    Asset::factory()->create(['purchase_price' => 2000]);

    Livewire::test(AssetValueWidget::class)
        ->assertSeeText('Valor total del inventario')
        ->assertSeeText(format_currency(3000))
        ->assertSeeText('Precio promedio por activo')
        ->assertSeeText(format_currency(1500));
});

it('only sums the value of assigned assets for the assigned-value stat', function () {
    Asset::factory()->assigned()->create(['purchase_price' => 500]);
    Asset::factory()->available()->create(['purchase_price' => 1000]);

    Livewire::test(AssetValueWidget::class)
        ->assertSeeText('Valor en activos asignados')
        ->assertSeeText(format_currency(500));
});

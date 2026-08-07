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

it('blends assets with no currency and assets explicitly in the base currency into one total', function () {
    Asset::factory()->create(['purchase_price' => 1000, 'currency' => null]);
    Asset::factory()->create(['purchase_price' => 2000, 'currency' => 'USD']);

    Livewire::test(AssetValueWidget::class)
        ->assertSeeText('Valor total del inventario')
        ->assertSeeText(format_currency(3000))
        ->assertDontSeeText('(USD)');
});

it('shows a per-currency breakdown once assets use more than one currency', function () {
    Asset::factory()->create(['purchase_price' => 1000, 'currency' => 'USD']);
    Asset::factory()->create(['purchase_price' => 500, 'currency' => 'EUR']);

    Livewire::test(AssetValueWidget::class)
        ->assertSeeText('Valor total del inventario (USD)')
        ->assertSeeText(format_currency(1000, 'USD'))
        ->assertSeeText('Valor total del inventario (EUR)')
        ->assertSeeText(format_currency(500, 'EUR'));
});

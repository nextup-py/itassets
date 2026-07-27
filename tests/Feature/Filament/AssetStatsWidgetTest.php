<?php

use App\Filament\Widgets\AssetStatsWidget;
use App\Models\Asset;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('shows maintenance and retired counts', function () {
    Asset::factory()->maintenance()->create();
    Asset::factory()->retired()->create();

    Livewire::test(AssetStatsWidget::class)
        ->assertSeeText('En mantenimiento')
        ->assertSeeText('Dados de baja');
});

<?php

use App\Filament\Widgets\RecentAssetsWidget;
use App\Models\Asset;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('shows recently registered assets', function () {
    $asset = Asset::factory()->create();

    Livewire::test(RecentAssetsWidget::class)
        ->assertCanSeeTableRecords([$asset]);
});

it('shows at most 5 assets, prioritizing the most recently created', function () {
    $oldest = Asset::factory()->count(3)->sequence(
        ['created_at' => now()->subDays(10)],
        ['created_at' => now()->subDays(9)],
        ['created_at' => now()->subDays(8)],
    )->create();
    $newest = Asset::factory()->count(5)->sequence(
        ['created_at' => now()->subDays(5)],
        ['created_at' => now()->subDays(4)],
        ['created_at' => now()->subDays(3)],
        ['created_at' => now()->subDays(2)],
        ['created_at' => now()->subDays(1)],
    )->create();

    Livewire::test(RecentAssetsWidget::class)
        ->assertCanSeeTableRecords($newest)
        ->assertCanNotSeeTableRecords($oldest);
});

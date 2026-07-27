<?php

use App\Filament\Widgets\AssetsByCategoryChartWidget;
use App\Models\AssetCategory;
use App\Models\Asset;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('groups asset counts by category', function () {
    $category = AssetCategory::factory()->create(['name' => 'Hardware']);
    Asset::factory()->count(3)->create(['asset_category_id' => $category->id]);

    $widget = Livewire::test(AssetsByCategoryChartWidget::class)->instance();
    $data = (new ReflectionMethod($widget, 'getData'))->invoke($widget);

    $index = array_search('Hardware', $data['labels']);

    expect($data['datasets'][0]['data'][$index])->toBe(3);
});

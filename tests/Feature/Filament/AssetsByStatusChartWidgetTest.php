<?php

use App\Filament\Widgets\AssetsByStatusChartWidget;
use App\Models\Asset;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('groups asset counts by status', function () {
    Asset::factory()->count(2)->available()->create();
    Asset::factory()->maintenance()->create();

    $widget = Livewire::test(AssetsByStatusChartWidget::class)->instance();
    $data = (new ReflectionMethod($widget, 'getData'))->invoke($widget);

    $statusIndex = array_search('En stock / Almacén', $data['labels']);
    $availableIndex = array_search('Disponible', $data['labels']);
    $maintenanceIndex = array_search('En mantenimiento', $data['labels']);

    expect($data['datasets'][0]['data'][$availableIndex])->toBe(2)
        ->and($data['datasets'][0]['data'][$maintenanceIndex])->toBe(1)
        ->and($data['datasets'][0]['data'][$statusIndex])->toBe(0);
});

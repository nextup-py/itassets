<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\AssetCategory;
use Filament\Widgets\ChartWidget;

class AssetsByCategoryChartWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 6;

    protected ?string $pollingInterval = '60s';

    protected ?string $heading = 'Activos por categoría';

    protected function getDateRange(): ?array
    {
        $from = session('dashboard_date_from');
        $to = session('dashboard_date_to');

        if ($from && $to) {
            return [$from, $to];
        }

        return null;
    }

    protected function getData(): array
    {
        $labels = [];
        $data   = [];

        $range = $this->getDateRange();

        $categories = AssetCategory::withCount(['assets' => function ($q) use ($range) {
            if ($range) {
                $q->whereBetween('created_at', [$range[0], $range[1] . ' 23:59:59']);
            }
        }])->get();

        foreach ($categories as $category) {
            $labels[] = $category->name;
            $data[]   = $category->assets_count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Cantidad',
                    'data'  => $data,
                    'backgroundColor' => '#3b82f6',
                    'borderColor'     => '#2563eb',
                    'borderWidth'     => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }


}

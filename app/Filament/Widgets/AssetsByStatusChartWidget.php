<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use Filament\Widgets\ChartWidget;

class AssetsByStatusChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 6;

    protected ?string $pollingInterval = '60s';

    protected ?string $heading = 'Activos por estado';

    protected function getData(): array
    {
        $colorMap = [
            'stock'       => '#9ca3af',
            'available'   => '#10b981',
            'assigned'    => '#3b82f6',
            'maintenance' => '#f59e0b',
            'retired'     => '#6b7280',
            'lost'        => '#ef4444',
        ];

        $counts = Asset::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $data   = [];
        $colors = [];
        $labels = [];

        foreach (Asset::STATUSES as $key => $label) {
            $count = (int) ($counts[$key] ?? 0);
            $data[]   = $count;
            $labels[] = $label;
            $colors[] = $colorMap[$key] ?? '#9ca3af';
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

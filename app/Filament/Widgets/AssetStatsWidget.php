<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AssetStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $counts = Asset::selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status');

        $total       = array_sum($counts->toArray());
        $available   = (int) ($counts['available'] ?? 0) + (int) ($counts['stock'] ?? 0);
        $assigned    = (int) ($counts['assigned'] ?? 0);
        $maintenance = (int) ($counts['maintenance'] ?? 0);
        $retired     = (int) ($counts['retired'] ?? 0);

        return [
            Stat::make('Total de activos', $total)
                ->icon('heroicon-o-computer-desktop')
                ->color('gray'),

            Stat::make('Disponibles', $available)
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Asignados', $assigned)
                ->icon('heroicon-o-user')
                ->color('primary'),

            Stat::make('En mantenimiento', $maintenance)
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('warning'),

            Stat::make('Dados de baja', $retired)
                ->icon('heroicon-o-archive-box-x-mark')
                ->color('danger'),
        ];
    }
}

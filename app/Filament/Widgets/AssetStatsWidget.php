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

    protected function getDateRange(): ?array
    {
        $from = session('dashboard_date_from');
        $to = session('dashboard_date_to');

        if ($from && $to) {
            return [$from, $to];
        }

        return null;
    }

    protected function getStats(): array
    {
        $query = Asset::query();

        $range = $this->getDateRange();
        if ($range) {
            $query->whereBetween('created_at', [$range[0], $range[1] . ' 23:59:59']);
        }

        $counts = (clone $query)->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status');

        $total       = array_sum($counts->toArray());
        $available   = (int) ($counts['available'] ?? 0);
        $stock       = (int) ($counts['stock'] ?? 0);
        $assigned    = (int) ($counts['assigned'] ?? 0);
        $maintenance = (int) ($counts['maintenance'] ?? 0);
        $retired     = (int) ($counts['retired'] ?? 0);
        $lost        = (int) ($counts['lost'] ?? 0);

        return [
            Stat::make('Total de activos', $total)
                ->icon('heroicon-o-computer-desktop')
                ->color('gray'),

            Stat::make('Disponibles', $available)
                ->description("{$stock} en stock / almacén")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Asignados', $assigned)
                ->icon('heroicon-o-user')
                ->color('primary'),

            Stat::make('En mantenimiento', $maintenance)
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('warning'),

            Stat::make('Dados de baja', $retired)
                ->description("{$lost} perdidos / robados")
                ->icon('heroicon-o-archive-box-x-mark')
                ->color('danger'),
        ];
    }
}

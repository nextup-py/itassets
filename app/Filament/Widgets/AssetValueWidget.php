<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AssetValueWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $totalValue = (float) Asset::sum('purchase_price');
        $avgPrice = Asset::whereNotNull('purchase_price')->avg('purchase_price');
        $totalAssets = Asset::count();
        $assignedValue = (float) Asset::where('status', 'assigned')->sum('purchase_price');
        $maintenanceCost = (float) Asset::where('status', 'maintenance')->sum('purchase_price');

        return [
            Stat::make('Valor total del inventario', \format_currency($totalValue))
                ->description("{$totalAssets} activos registrados")
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Valor en activos asignados', \format_currency($assignedValue))
                ->icon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make('Precio promedio por activo', \format_currency($avgPrice ?: 0))
                ->icon('heroicon-o-calculator')
                ->color('gray'),
        ];
    }
}

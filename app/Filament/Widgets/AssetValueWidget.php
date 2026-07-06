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
            Stat::make('Valor total del inventario', '$' . number_format($totalValue, 2))
                ->description("{$totalAssets} activos registrados")
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Valor en activos asignados', '$' . number_format($assignedValue, 2))
                ->icon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make('Precio promedio por activo', '$' . number_format($avgPrice ?: 0, 2))
                ->icon('heroicon-o-calculator')
                ->color('gray'),
        ];
    }
}

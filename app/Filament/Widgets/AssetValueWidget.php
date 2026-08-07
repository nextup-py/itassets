<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\Setting;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AssetValueWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    /**
     * Assets can carry their own `currency` (nullable — defaults to the
     * installation's base_currency when unset). Summing raw purchase_price
     * across mixed currencies would produce a meaningless blended number, so
     * priced assets are grouped by currency first. In the common case (every
     * asset in one currency, the default for installations that never touch
     * the field) this renders identically to a single blind sum — the
     * per-currency breakdown only appears once a second currency shows up.
     */
    protected function getStats(): array
    {
        $baseCurrency = Setting::get('base_currency', 'USD');
        $totalAssets = Asset::count();

        $priced = Asset::query()->whereNotNull('purchase_price')->get(['purchase_price', 'currency', 'status']);
        $byCurrency = $priced->groupBy(fn (Asset $asset) => $asset->currency ?: $baseCurrency);
        $multiCurrency = $byCurrency->count() > 1;

        $stats = [];

        foreach ($byCurrency as $currency => $assets) {
            $suffix = $multiCurrency ? " ({$currency})" : '';

            $stats[] = Stat::make("Valor total del inventario{$suffix}", \format_currency((float) $assets->sum('purchase_price'), $currency))
                ->description($multiCurrency ? "{$assets->count()} activos con precio" : "{$totalAssets} activos registrados")
                ->icon('heroicon-o-banknotes')
                ->color('success');

            $stats[] = Stat::make("Valor en activos asignados{$suffix}", \format_currency((float) $assets->where('status', 'assigned')->sum('purchase_price'), $currency))
                ->icon('heroicon-o-user-group')
                ->color('primary');

            $stats[] = Stat::make("Precio promedio por activo{$suffix}", \format_currency($assets->avg('purchase_price') ?: 0, $currency))
                ->icon('heroicon-o-calculator')
                ->color('gray');
        }

        if ($stats === []) {
            $stats = [
                Stat::make('Valor total del inventario', \format_currency(0))
                    ->description("{$totalAssets} activos registrados")
                    ->icon('heroicon-o-banknotes')
                    ->color('success'),

                Stat::make('Valor en activos asignados', \format_currency(0))
                    ->icon('heroicon-o-user-group')
                    ->color('primary'),

                Stat::make('Precio promedio por activo', \format_currency(0))
                    ->icon('heroicon-o-calculator')
                    ->color('gray'),
            ];
        }

        return $stats;
    }
}

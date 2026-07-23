<?php

namespace App\Filament\Pages;

use App\Exports\AssetsExport;
use App\Exports\AssignmentsExport;
use App\Models\Asset;
use App\Models\AssetCategory;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;

class Reports extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static ?string $navigationLabel = 'Reportes';

    protected static \UnitEnum|string|null $navigationGroup = 'Inventario';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.reports';

    public function getTotalAssetsProperty(): int
    {
        return Asset::count();
    }

    public function getAvailableAssetsProperty(): int
    {
        return Asset::whereIn('status', ['available', 'stock'])->count();
    }

    public function getAssignedAssetsProperty(): int
    {
        return Asset::where('status', 'assigned')->count();
    }

    public function getMaintenanceAssetsProperty(): int
    {
        return Asset::where('status', 'maintenance')->count();
    }

    public function getRetiredAssetsProperty(): int
    {
        return Asset::where('status', 'retired')->count();
    }

    public function getTotalValueProperty(): float
    {
        return (float) Asset::sum('purchase_price');
    }

    public function exportAssetsAction(): Action
    {
        return Action::make('exportAssets')
            ->label('Exportar activos')
            ->icon('heroicon-o-document-arrow-down')
            ->color('success')
            ->visible(fn () => auth()->user()?->can('export_report') ?? false)
            ->form([
                Select::make('status')
                    ->label('Filtrar por estado')
                    ->options(['' => 'Todos los estados', ...Asset::STATUSES])
                    ->default(''),
                Select::make('category_id')
                    ->label('Filtrar por categoría')
                    ->options(['' => 'Todas las categorías', ...AssetCategory::pluck('name', 'id')->toArray()])
                    ->default(''),
            ])
            ->action(function (array $data): void {
                $export = new AssetsExport(
                    status: $data['status'] ?: null,
                    categoryId: $data['category_id'] ?: null,
                );

                Excel::download($export, 'reporte_activos.xlsx');

                Notification::make()
                    ->title('Descargando reporte de activos')
                    ->success()
                    ->send();
            });
    }

    public function exportAssignmentsAction(): Action
    {
        return Action::make('exportAssignments')
            ->label('Exportar asignaciones')
            ->icon('heroicon-o-document-arrow-down')
            ->color('warning')
            ->visible(fn () => auth()->user()?->can('export_report') ?? false)
            ->form([
                Select::make('status')
                    ->label('Filtrar por estado')
                    ->options([
                        ''         => 'Todas las asignaciones',
                        'active'   => 'Solo activas',
                        'returned' => 'Solo devueltas',
                    ])
                    ->default('active'),
            ])
            ->action(function (array $data): void {
                $export = new AssignmentsExport(
                    status: $data['status'] ?: null,
                );

                Excel::download($export, 'reporte_asignaciones.xlsx');

                Notification::make()
                    ->title('Descargando reporte de asignaciones')
                    ->success()
                    ->send();
            });
    }
}

<?php

namespace App\Filament\Resources\Assets\Pages;

use App\Exports\AssetsExport;
use App\Exports\AssetTemplateExport;
use App\Filament\Resources\Assets\AssetResource;
use App\Imports\AssetImport;
use App\Models\Asset;
use App\Models\AssetCategory;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListAssets extends ListRecords
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ── Descargar plantilla ──────────────────────────────────────────
            Action::make('downloadTemplate')
                ->label('Plantilla CSV')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->visible(fn () => auth()->user()?->can('import_asset') ?? false)
                ->action(function () {
                    return Excel::download(new AssetTemplateExport, 'plantilla_activos.csv');
                }),

            // ── Importar activos ─────────────────────────────────────────────
            Action::make('importAssets')
                ->label('Importar desde CSV / Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->visible(fn () => auth()->user()?->can('import_asset') ?? false)
                ->form([
                    FileUpload::make('file')
                        ->label('Archivo')
                        ->acceptedFileTypes([
                            'text/csv',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->maxSize(10240)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $file = $data['file'];

                    try {
                        $path = $file instanceof \Livewire\TemporaryUploadedFile
                            ? $file->getRealPath()
                            : storage_path('app/' . $file);

                        $import = new AssetImport;
                        Excel::import($import, $path);

                        Notification::make()
                            ->title('Importación completada')
                            ->body('Los activos se importaron correctamente.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al importar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // ── Exportar activos ─────────────────────────────────────────────
            Action::make('exportAssets')
                ->label('Exportar')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
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

                    Excel::download($export, 'activos.xlsx');

                    Notification::make()
                        ->title('Descargando activos')
                        ->success()
                        ->send();
                }),

            CreateAction::make()
                ->label('Nuevo activo'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Supplies\Pages;

use App\Exports\SuppliesExport;
use App\Exports\SupplyTemplateExport;
use App\Filament\Resources\Supplies\SupplyResource;
use App\Imports\SupplyImport;
use App\Models\AssetCategory;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListSupplies extends ListRecords
{
    protected static string $resource = SupplyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadTemplate')
                ->label('Plantilla CSV')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->visible(fn () => auth()->user()?->can('import_supply') ?? false)
                ->action(function () {
                    return Excel::download(new SupplyTemplateExport, 'plantilla_insumos.csv');
                }),

            Action::make('importSupplies')
                ->label('Importar desde CSV / Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->visible(fn () => auth()->user()?->can('import_supply') ?? false)
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
                            : Storage::disk('local')->path($file);

                        $import = new SupplyImport;
                        Excel::import($import, $path);

                        $skippedCount = $import->failures()->count() + $import->errors()->count();

                        if ($skippedCount > 0) {
                            Notification::make()
                                ->title('Importación completada con filas omitidas')
                                ->body("Se importaron los datos válidos. {$skippedCount} fila(s) se omitieron por errores de validación o datos inválidos.")
                                ->warning()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Importación completada')
                                ->body('Los insumos se importaron correctamente.')
                                ->success()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al importar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('exportSupplies')
                ->label('Exportar')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->visible(fn () => auth()->user()?->can('export_report') ?? false)
                ->form([
                    Select::make('category_id')
                        ->label('Filtrar por categoría')
                        ->options(['' => 'Todas las categorías', ...AssetCategory::pluck('name', 'id')->toArray()])
                        ->default(''),
                ])
                ->action(function (array $data): void {
                    $export = new SuppliesExport(
                        categoryId: $data['category_id'] ?: null,
                    );

                    Excel::download($export, 'insumos.xlsx');

                    Notification::make()
                        ->title('Descargando insumos')
                        ->success()
                        ->send();
                }),

            CreateAction::make(),
        ];
    }
}

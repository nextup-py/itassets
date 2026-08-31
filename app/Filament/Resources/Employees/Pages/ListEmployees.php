<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Exports\EmployeesExport;
use App\Exports\EmployeeTemplateExport;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Imports\EmployeeImport;
use App\Models\Department;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadTemplate')
                ->label('Plantilla CSV')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->visible(fn () => auth()->user()?->can('import_employee') ?? false)
                ->action(function () {
                    return Excel::download(new EmployeeTemplateExport, 'plantilla_empleados.csv');
                }),

            Action::make('importEmployees')
                ->label('Importar desde CSV / Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->visible(fn () => auth()->user()?->can('import_employee') ?? false)
                ->form([
                    FileUpload::make('file')
                        ->label('Archivo')
                        ->acceptedFileTypes([
                            'text/csv',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->maxSize(10240)
                        ->helperText('CSV o Excel, máx. 10MB.')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $file = $data['file'];

                    try {
                        $path = $file instanceof \Livewire\TemporaryUploadedFile
                            ? $file->getRealPath()
                            : Storage::disk('local')->path($file);

                        $import = new EmployeeImport;
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
                                ->body('Los empleados se importaron correctamente.')
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

            Action::make('exportEmployees')
                ->label('Exportar')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->visible(fn () => auth()->user()?->can('export_report') ?? false)
                ->form([
                    Select::make('department_id')
                        ->label('Filtrar por departamento')
                        ->options(['' => 'Todos los departamentos', ...Department::pluck('name', 'id')->toArray()])
                        ->default(''),
                    Select::make('is_active')
                        ->label('Filtrar por estado')
                        ->options(['' => 'Todos', '1' => 'Activos', '0' => 'Inactivos'])
                        ->default(''),
                ])
                ->action(function (array $data): void {
                    $export = new EmployeesExport(
                        departmentId: $data['department_id'] ?: null,
                        isActive: $data['is_active'] === '' ? null : (bool) $data['is_active'],
                    );

                    Excel::download($export, 'empleados.xlsx');

                    Notification::make()
                        ->title('Descargando empleados')
                        ->success()
                        ->send();
                }),

            CreateAction::make(),
        ];
    }
}

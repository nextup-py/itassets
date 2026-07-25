<?php

namespace App\Filament\Resources\Assets\Pages;

use App\Filament\Resources\Assets\AssetResource;
use App\Models\MaintenanceRecord;
use App\Services\AssignmentService;
use App\Services\MaintenanceService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewAsset extends ViewRecord
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        $assignmentService = app(AssignmentService::class);
        $maintenanceService = app(MaintenanceService::class);

        return [
            Action::make('printAssignment')
                ->label('Imprimir asignación')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->visible(fn () => $this->record->status === 'assigned' && $this->record->activeAssignment())
                ->url(fn () => route('assignments.pdf', $this->record->activeAssignment()), shouldOpenInNewTab: true),

            Action::make('assign')
                ->label('Asignar activo')
                ->icon('heroicon-o-user-plus')
                ->color('success')
                ->authorize('update_asset')
                ->visible(fn () => ! in_array($this->record->status, ['assigned', 'maintenance', 'retired', 'lost']))
                ->form([
                    Select::make('employee_id')
                        ->label('Empleado')
                        ->required()
                        ->options(fn () => $assignmentService->getActiveEmployees())
                        ->searchable(),

                    DatePicker::make('assigned_at')
                        ->label('Fecha de asignación')
                        ->required()
                        ->default(now())
                        ->displayFormat('d/m/Y'),

                    TextInput::make('charger_serial')
                        ->label('Cargador N/S')
                        ->maxLength(100),

                    TextInput::make('ticket_number')
                        ->label('N.º Ticket')
                        ->maxLength(100),

                    Textarea::make('notes')
                        ->label('Notas')
                        ->rows(2),
                ])
                ->action(function (array $data) use ($assignmentService): void {
                    $assignmentService->assign($this->record, $data);

                    Notification::make()
                        ->title('Activo asignado correctamente')
                        ->success()
                        ->send();
                }),

            Action::make('return')
                ->label('Registrar devolución')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->authorize('update_asset')
                ->visible(fn () => $this->record->status === 'assigned')
                ->form([
                    DatePicker::make('returned_at')
                        ->label('Fecha de devolución')
                        ->required()
                        ->default(now())
                        ->displayFormat('d/m/Y'),

                    Textarea::make('notes')
                        ->label('Notas de devolución')
                        ->rows(2),
                ])
                ->action(function (array $data) use ($assignmentService): void {
                    $assignmentService->return($this->record, $data);

                    Notification::make()
                        ->title('Devolución registrada correctamente')
                        ->success()
                        ->send();
                }),

            Action::make('sendToMaintenance')
                ->label('Enviar a mantenimiento')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('warning')
                ->authorize('update_asset')
                ->visible(fn () => ! in_array($this->record->status, ['maintenance', 'retired', 'lost']))
                ->form([
                    Select::make('type')
                        ->label('Tipo de mantenimiento')
                        ->required()
                        ->options(MaintenanceRecord::TYPES),

                    Textarea::make('description')
                        ->label('Descripción del problema / motivo')
                        ->required()
                        ->rows(3),

                    TextInput::make('technician')
                        ->label('Técnico responsable')
                        ->maxLength(150),

                    Select::make('supplier_id')
                        ->label('Proveedor de servicio')
                        ->options(fn () => $maintenanceService->getSuppliers())
                        ->searchable(),

                    DatePicker::make('started_at')
                        ->label('Fecha de inicio')
                        ->required()
                        ->default(now())
                        ->displayFormat('d/m/Y'),
                ])
                ->action(function (array $data) use ($maintenanceService): void {
                    $maintenanceService->start($this->record, $data);

                    Notification::make()
                        ->title('Activo enviado a mantenimiento')
                        ->success()
                        ->send();
                }),

            Action::make('closeMaintenance')
                ->label('Cerrar mantenimiento')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->authorize('update_asset')
                ->visible(fn () => $this->record->status === 'maintenance')
                ->form([
                    Select::make('new_asset_status')
                        ->label('Nuevo estado del activo')
                        ->required()
                        ->options([
                            'available' => 'Disponible',
                            'retired'   => 'Dado de baja',
                            'lost'      => 'Perdido / Robado',
                        ])
                        ->default('available'),

                    DatePicker::make('completed_at')
                        ->label('Fecha de término')
                        ->required()
                        ->default(now())
                        ->displayFormat('d/m/Y'),

                    Textarea::make('resolution')
                        ->label('Resolución / Diagnóstico final')
                        ->rows(3),
                ])
                ->action(function (array $data) use ($maintenanceService): void {
                    $maintenanceService->close($this->record, $data);

                    Notification::make()
                        ->title('Mantenimiento cerrado correctamente')
                        ->success()
                        ->send();
                }),

            EditAction::make()
                ->label('Editar'),
        ];
    }
}

<?php

namespace App\Filament\Resources\MaintenanceRecords\Pages;

use App\Filament\Resources\MaintenanceRecords\MaintenanceRecordResource;
use App\Models\MaintenanceRecord;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewMaintenanceRecord extends ViewRecord
{
    protected static string $resource = MaintenanceRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('complete')
                ->label('Marcar completado')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->authorize('update_maintenance_record')
                ->visible(fn (MaintenanceRecord $record): bool => $record->status !== 'completed')
                ->requiresConfirmation()
                ->action(function (MaintenanceRecord $record): void {
                    $record->update([
                        'status' => 'completed',
                        'completed_at' => $record->completed_at ?? now(),
                    ]);

                    Notification::make()->success()->title('Mantenimiento marcado como completado')->send();
                }),

            EditAction::make(),
        ];
    }
}

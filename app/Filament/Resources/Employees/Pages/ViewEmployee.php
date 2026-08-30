<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Assignments\AssignmentResource;
use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('newAssignment')
                ->label('Nueva asignación')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->authorize('create_assignment')
                ->url(fn () => AssignmentResource::getUrl('create') . '?employee_id=' . $this->record->id),
            EditAction::make(),
        ];
    }
}

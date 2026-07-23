<?php

namespace App\Filament\Resources\Assignments\Pages;

use App\Filament\Resources\Assignments\AssignmentResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAssignment extends ViewRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('PDF')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('assignments.pdf', $this->record), shouldOpenInNewTab: true),
            EditAction::make(),
        ];
    }
}

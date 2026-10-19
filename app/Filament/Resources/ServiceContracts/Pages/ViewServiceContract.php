<?php

namespace App\Filament\Resources\ServiceContracts\Pages;

use App\Filament\Resources\ServiceContracts\ServiceContractResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceContract extends ViewRecord
{
    protected static string $resource = ServiceContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

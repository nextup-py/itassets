<?php

namespace App\Filament\Resources\ServiceContracts\Pages;

use App\Filament\Resources\ServiceContracts\ServiceContractResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceContract extends CreateRecord
{
    protected static string $resource = ServiceContractResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Creado';
    }
}

<?php

namespace App\Filament\Resources\MaintenanceRecords\Pages;

use App\Filament\Resources\MaintenanceRecords\MaintenanceRecordResource;
use App\Services\MaintenanceService;
use Filament\Resources\Pages\CreateRecord;

class CreateMaintenanceRecord extends CreateRecord
{
    protected static string $resource = MaintenanceRecordResource::class;

    protected ?string $pendingAssetStatus = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pendingAssetStatus = $data['new_asset_status'] ?? null;
        unset($data['new_asset_status']);

        return $data;
    }

    protected function afterCreate(): void
    {
        app(MaintenanceService::class)->syncAssetStatus($this->record, $this->pendingAssetStatus);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Creado';
    }
}

<?php

namespace App\Filament\Resources\Assignments\Pages;

use App\Filament\Resources\Assignments\AssignmentResource;
use App\Services\AssignmentService;
use Filament\Resources\Pages\CreateRecord;

class CreateAssignment extends CreateRecord
{
    protected static string $resource = AssignmentResource::class;

    protected array $assetsToAttach = [];

    public function mount(): void
    {
        parent::mount();

        $employeeId = request()->query('employee_id');
        if ($employeeId) {
            $this->data['employee_id'] = (int) $employeeId;
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->assetsToAttach = $data['assets'] ?? [];
        unset($data['assets']);
        $data['assigned_by'] = auth()->user()?->name;
        return $data;
    }

    protected function afterCreate(): void
    {
        foreach ($this->assetsToAttach as $item) {
            $this->record->assets()->attach($item['asset_id'], [
                'charger_serial' => $item['charger_serial'] ?? null,
                'ticket_number'  => $item['ticket_number'] ?? null,
                'assigned_at'    => $this->record->assigned_at,
            ]);
        }

        app(AssignmentService::class)->notifyEmployee($this->record);
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

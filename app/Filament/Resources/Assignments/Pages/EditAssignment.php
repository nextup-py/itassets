<?php

namespace App\Filament\Resources\Assignments\Pages;

use App\Filament\Resources\Assignments\AssignmentResource;
use App\Models\Asset;
use App\Models\Assignment;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAssignment extends EditRecord
{
    protected static string $resource = AssignmentResource::class;

    protected array $assetsToSync = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['assets'] = $this->record->assets->map(fn ($asset) => [
            'asset_id'       => $asset->id,
            'charger_serial' => $asset->pivot->charger_serial,
            'ticket_number'  => $asset->pivot->ticket_number,
        ])->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->assetsToSync = $data['assets'] ?? [];
        unset($data['assets']);
        return Assignment::normalizeRecipientData($data);
    }

    protected function afterSave(): void
    {
        $existingIds = $this->record->assets->pluck('id')->toArray();
        $newIds = collect($this->assetsToSync)->pluck('asset_id')->toArray();

        $toDetach = array_diff($existingIds, $newIds);
        if (! empty($toDetach)) {
            $this->record->assets()->detach($toDetach);
            Asset::whereIn('id', $toDetach)
                ->where('status', 'assigned')
                ->update(['status' => 'available']);
        }

        foreach ($this->assetsToSync as $item) {
            if (in_array($item['asset_id'], $existingIds)) {
                $this->record->assets()->updateExistingPivot($item['asset_id'], [
                    'charger_serial' => $item['charger_serial'] ?? null,
                    'ticket_number'  => $item['ticket_number'] ?? null,
                ]);
            } else {
                $this->record->assets()->attach($item['asset_id'], [
                    'charger_serial' => $item['charger_serial'] ?? null,
                    'ticket_number'  => $item['ticket_number'] ?? null,
                    'assigned_at'    => $this->record->assigned_at,
                ]);
            }
        }
    }
}

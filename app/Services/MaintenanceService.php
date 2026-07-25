<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\MaintenanceRecord;
use App\Models\Supplier;

class MaintenanceService
{
    public function start(Asset $asset, array $data): MaintenanceRecord
    {
        $record = MaintenanceRecord::create([
            'asset_id'    => $asset->id,
            'type'        => $data['type'],
            'status'      => 'in_progress',
            'description' => $data['description'],
            'technician'  => $data['technician'] ?? null,
            'supplier_id' => $data['supplier_id'] ?? null,
            'started_at'  => $data['started_at'],
        ]);

        $asset->refresh();

        return $record;
    }

    public function close(Asset $asset, array $data): void
    {
        $active = $asset->maintenanceRecords()
            ->where('status', '!=', 'completed')
            ->latest('started_at')
            ->first();

        if ($active) {
            $active->update([
                'status'       => 'completed',
                'completed_at' => $data['completed_at'],
                'resolution'   => $data['resolution'] ?? null,
            ]);
        }

        $asset->update(['status' => $data['new_asset_status']]);
        $asset->refresh();
    }

    public function getSuppliers(): array
    {
        return Supplier::pluck('name', 'id')->toArray();
    }

    public function syncAssetStatus(MaintenanceRecord $record, ?string $newAssetStatus): void
    {
        if (! $newAssetStatus || $record->status !== 'completed' || ! $record->asset) {
            return;
        }

        $record->asset->update(['status' => $newAssetStatus]);
    }
}

<?php

namespace App\Services;

use App\Models\Supply;
use App\Models\SupplyMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SupplyMovementService
{
    /**
     * Entrega: only decreases the delivered supply's stock.
     * Reemplazo: also increases the received supply's "damaged" bucket
     * (defaults to the same supply when no received_supply_id is given).
     */
    public function register(Supply $supply, array $data): SupplyMovement
    {
        $quantity = (int) $data['quantity'];

        return DB::transaction(function () use ($supply, $data, $quantity) {
            // Locked re-read: two concurrent registrations against the same supply,
            // both near its stock limit, could otherwise each pass a stale check
            // before either commits and over-draw it, the same class of race
            // LicenseAssignment::save() locks the parent License against.
            $supply = Supply::whereKey($supply->id)->lockForUpdate()->first();

            if ($quantity > $supply->quantity_available) {
                throw new RuntimeException("No hay stock suficiente de \"{$supply->name}\" (disponible: {$supply->quantity_available}).");
            }

            $supply->decrement('quantity_available', $quantity);

            if ($data['type'] === 'reemplazo') {
                $receivedSupply = ! empty($data['received_supply_id'])
                    ? Supply::query()->findOrFail($data['received_supply_id'])
                    : $supply;

                $receivedSupply->increment('quantity_damaged', $quantity);
            }

            return SupplyMovement::create([
                'supply_id' => $supply->id,
                'type' => $data['type'],
                'quantity' => $quantity,
                'employee_id' => $data['employee_id'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'received_supply_id' => $data['type'] === 'reemplazo' ? ($data['received_supply_id'] ?? $supply->id) : null,
                'performed_at' => $data['performed_at'],
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }
}

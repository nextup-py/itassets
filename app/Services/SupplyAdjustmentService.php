<?php

namespace App\Services;

use App\Models\Supply;
use App\Models\SupplyAdjustment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SupplyAdjustmentService
{
    public function register(Supply $supply, array $data): SupplyAdjustment
    {
        $quantity = (int) $data['quantity'];

        if ($quantity > $supply->quantity_available) {
            throw new RuntimeException("No hay stock suficiente de \"{$supply->name}\" para dar de baja (disponible: {$supply->quantity_available}).");
        }

        return DB::transaction(function () use ($supply, $data, $quantity) {
            $supply->decrement('quantity_available', $quantity);

            return SupplyAdjustment::create([
                'supply_id' => $supply->id,
                'quantity' => $quantity,
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }
}

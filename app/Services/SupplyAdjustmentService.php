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
        $target = $data['target'] ?? 'available';
        $column = $target === 'damaged' ? 'quantity_damaged' : 'quantity_available';

        return DB::transaction(function () use ($supply, $data, $quantity, $target, $column) {
            // Locked re-read, same reasoning as SupplyMovementService::register().
            $supply = Supply::whereKey($supply->id)->lockForUpdate()->first();

            if ($quantity > $supply->{$column}) {
                $label = $target === 'damaged' ? 'insumos dañados' : 'stock';
                throw new RuntimeException("No hay suficiente {$label} de \"{$supply->name}\" para dar de baja (disponible: {$supply->{$column}}).");
            }

            $supply->decrement($column, $quantity);

            return SupplyAdjustment::create([
                'supply_id' => $supply->id,
                'quantity' => $quantity,
                'target' => $target,
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }
}

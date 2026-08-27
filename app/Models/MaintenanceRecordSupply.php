<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use RuntimeException;

class MaintenanceRecordSupply extends Pivot
{
    use HasFactory;

    protected $table = 'maintenance_record_supply';

    protected $fillable = [
        'maintenance_record_id',
        'supply_id',
        'quantity_used',
    ];

    protected $casts = [
        'quantity_used' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (MaintenanceRecordSupply $pivot) {
            $supply = $pivot->supply ?? Supply::find($pivot->supply_id);

            if (! $supply || $pivot->quantity_used > $supply->quantity_available) {
                $available = $supply?->quantity_available ?? 0;
                throw new RuntimeException("No hay stock suficiente de \"{$supply?->name}\" (disponible: {$available}).");
            }
        });

        static::created(function (MaintenanceRecordSupply $pivot) {
            $pivot->supply?->decrement('quantity_available', $pivot->quantity_used);
        });

        static::deleted(function (MaintenanceRecordSupply $pivot) {
            $pivot->supply?->increment('quantity_available', $pivot->quantity_used);
        });
    }

    public function maintenanceRecord(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRecord::class);
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }
}

<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SupplyAdjustment extends Model
{
    use Blameable, HasFactory, LogsActivity;

    protected $fillable = [
        'supply_id',
        'quantity',
        'target',
        'reason',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public const REASONS = [
        'loss' => 'Pérdida',
        'damage' => 'Daño irreparable',
        'theft' => 'Robo',
        'other' => 'Otro',
    ];

    // Reasons for the 'damaged' target only (disposing of units already sitting
    // in quantity_damaged) — distinct from REASONS above, which explain how a
    // unit left quantity_available in the first place.
    public const DISPOSAL_REASONS = [
        'disposed' => 'Descartado',
        'returned_to_supplier' => 'Devuelto al proveedor',
        'other' => 'Otro',
    ];

    public const TARGETS = [
        'available' => 'Stock disponible',
        'damaged' => 'Insumos dañados',
    ];

    public function getReasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? self::DISPOSAL_REASONS[$this->reason] ?? $this->reason;
    }

    public function getTargetLabel(): string
    {
        return self::TARGETS[$this->target] ?? $this->target;
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}

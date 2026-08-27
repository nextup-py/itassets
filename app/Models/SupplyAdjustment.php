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

    public function getReasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
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

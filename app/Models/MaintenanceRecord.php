<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MaintenanceRecord extends Model
{
    use HasFactory, Blameable, LogsActivity;

    protected $fillable = [
        'asset_id',
        'type',
        'status',
        'description',
        'technician',
        'supplier_id',
        'cost',
        'started_at',
        'completed_at',
        'resolution',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'started_at'   => 'date',
        'completed_at' => 'date',
        'cost'         => 'decimal:2',
    ];

    public const TYPES = [
        'repair'      => 'Reparación',
        'preventive'  => 'Mantenimiento preventivo',
        'warranty'    => 'Garantía',
        'upgrade'     => 'Actualización / Upgrade',
        'other'       => 'Otro',
    ];

    public const STATUSES = [
        'pending'     => 'Pendiente',
        'in_progress' => 'En proceso',
        'completed'   => 'Completado',
    ];

    public const STATUS_COLORS = [
        'pending'     => 'warning',
        'in_progress' => 'info',
        'completed'   => 'success',
    ];

    public const TYPE_COLORS = [
        'repair'     => 'danger',
        'preventive' => 'success',
        'warranty'   => 'warning',
        'upgrade'    => 'info',
        'other'      => 'gray',
    ];

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusBadgeColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    // -------------------------------------------------------------------------
    // Al crear un registro de mantenimiento → activo pasa a "En mantenimiento"
    // -------------------------------------------------------------------------
    protected static function booted(): void
    {
        static::created(function (MaintenanceRecord $record) {
            if ($record->status !== 'completed' && $record->asset) {
                $record->asset->update(['status' => 'maintenance']);
            }
        });

        static::updated(function (MaintenanceRecord $record) {
            if ($record->wasChanged('status') && $record->status === 'completed' && $record->asset) {
                $record->asset->update(['status' => 'available']);
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relaciones
    // -------------------------------------------------------------------------
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}

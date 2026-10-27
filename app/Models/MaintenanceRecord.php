<?php

namespace App\Models;

use App\Notifications\MaintenanceAlertNotification;
use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MaintenanceRecord extends Model
{
    use Blameable, HasFactory, LogsActivity;

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
        'started_at' => 'date',
        'completed_at' => 'date',
        'cost' => 'decimal:2',
    ];

    public const TYPES = [
        'repair' => 'Reparación',
        'preventive' => 'Mantenimiento preventivo',
        'warranty' => 'Garantía',
        'upgrade' => 'Actualización / Upgrade',
        'other' => 'Otro',
    ];

    public const STATUSES = [
        'pending' => 'Pendiente',
        'in_progress' => 'En proceso',
        'completed' => 'Completado',
    ];

    public const STATUS_COLORS = [
        'pending' => 'warning',
        'in_progress' => 'info',
        'completed' => 'success',
    ];

    public const TYPE_COLORS = [
        'repair' => 'danger',
        'preventive' => 'success',
        'warranty' => 'warning',
        'upgrade' => 'info',
        'other' => 'gray',
    ];

    public const PROLONGED_DAYS = 7;

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
    // Al pasar a "En proceso" → activo pasa a "En mantenimiento". "Pendiente"
    // no tiene ningún efecto sobre el activo: representa trabajo programado
    // que todavía no arrancó.
    // -------------------------------------------------------------------------
    protected static function booted(): void
    {
        static::saving(function (MaintenanceRecord $record) {
            if (blank($record->technician) && blank($record->supplier_id)) {
                throw ValidationException::withMessages([
                    'technician' => 'Debe indicar un técnico responsable y/o un proveedor de servicio.',
                ]);
            }
        });

        static::created(function (MaintenanceRecord $record) {
            if ($record->status === 'in_progress') {
                static::markStarted($record);
            }
        });

        static::updated(function (MaintenanceRecord $record) {
            if ($record->wasChanged('status') && $record->status === 'in_progress') {
                static::markStarted($record);
            }
        });

        static::deleted(function (MaintenanceRecord $record) {
            if ($record->status !== 'completed' && $record->asset && $record->asset->status === 'maintenance') {
                $stillActive = static::where('asset_id', $record->asset_id)
                    ->where('status', '!=', 'completed')
                    ->exists();

                if (! $stillActive) {
                    $record->asset->update(['status' => 'available']);
                }
            }
        });
    }

    private static function markStarted(MaintenanceRecord $record): void
    {
        if (! $record->asset) {
            return;
        }

        $record->asset->update(['status' => 'maintenance']);

        foreach (User::managers()->get() as $manager) {
            (new MaintenanceAlertNotification($record, 'started'))->sendToManager($manager);
        }
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

    public function supplies(): BelongsToMany
    {
        return $this->belongsToMany(Supply::class, 'maintenance_record_supply')
            ->using(MaintenanceRecordSupply::class)
            ->withPivot(['quantity_used'])
            ->withTimestamps();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}

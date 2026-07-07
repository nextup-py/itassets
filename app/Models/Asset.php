<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Asset extends Model
{
    use HasFactory, Blameable, LogsActivity;

    protected $fillable = [
        'asset_tag',
        'name',
        'asset_category_id',
        'brand',
        'model',
        'serial_number',
        'status',
        'condition',
        'photo',
        'notes',
        'purchase_date',
        'purchase_price',
        'supplier_id',
        'location_id',
        'warranty_expiry_date',
        'warranty_supplier_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'purchase_date'        => 'date',
        'warranty_expiry_date' => 'date',
        'purchase_price'       => 'decimal:2',
    ];

    public const STATUSES = [
        'stock'       => 'En stock / Almacén',
        'available'   => 'Disponible',
        'assigned'    => 'Asignado',
        'maintenance' => 'En mantenimiento',
        'retired'     => 'Dado de baja',
        'lost'        => 'Perdido / Robado',
    ];

    public const STATUS_COLORS = [
        'stock'       => 'info',
        'available'   => 'success',
        'assigned'    => 'primary',
        'maintenance' => 'warning',
        'retired'     => 'gray',
        'lost'        => 'danger',
    ];

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusBadgeColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public const CONDITIONS = [
        'new'  => 'Nuevo',
        'good' => 'Bueno',
        'fair' => 'Regular',
        'poor' => 'Deteriorado',
    ];

    protected static function booted(): void
    {
        static::creating(function (Asset $asset) {
            if (empty($asset->asset_tag)) {
                $lastNumber = static::query()
                    ->where('asset_tag', 'like', 'IT-%')
                    ->selectRaw("MAX(CAST(SUBSTRING(asset_tag, 4) AS UNSIGNED)) as max_num")
                    ->value('max_num') ?? 0;

                $asset->asset_tag = 'IT-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warrantySupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'warranty_supplier_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(Assignment::class, 'assignment_asset')
            ->using(AssignmentAsset::class)
            ->withPivot(['charger_serial', 'ticket_number', 'assigned_at', 'notes'])
            ->withTimestamps();
    }

    public function activeAssignment(): ?Assignment
    {
        return Assignment::whereHas('assets', fn ($q) => $q->where('asset_id', $this->id))
            ->whereNull('returned_at')
            ->latest('assigned_at')
            ->first();
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}

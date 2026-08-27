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

class Supply extends Model
{
    use Blameable, HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'asset_category_id',
        'quantity_available',
        'quantity_damaged',
        'supplier_id',
        'location_id',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity_available' => 'integer',
        'quantity_damaged' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    // Movements where this supply is the one delivered (stock leaving).
    public function movements(): HasMany
    {
        return $this->hasMany(SupplyMovement::class);
    }

    // Movements ('reemplazo') where this supply received the returned unit(s).
    public function incomingMovements(): HasMany
    {
        return $this->hasMany(SupplyMovement::class, 'received_supply_id');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(SupplyAdjustment::class);
    }

    public function maintenanceRecords(): BelongsToMany
    {
        return $this->belongsToMany(MaintenanceRecord::class, 'maintenance_record_supply')
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

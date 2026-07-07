<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class License extends Model
{
    use HasFactory, Blameable, LogsActivity;

    protected $fillable = [
        'product_name',
        'license_type',
        'license_key',
        'total_seats',
        'purchase_date',
        'expiry_date',
        'purchase_price',
        'supplier_id',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expiry_date'   => 'date',
        'purchase_price' => 'decimal:2',
        'total_seats'   => 'integer',
    ];

    public const TYPES = [
        'perpetual'    => 'Perpetua',
        'subscription' => 'Suscripción',
        'per_device'   => 'Por dispositivo',
        'per_user'     => 'Por usuario / sede',
        'concurrent'   => 'Concurrente',
    ];

    // -------------------------------------------------------------------------
    // Seats
    // -------------------------------------------------------------------------
    public function usedSeats(): int
    {
        if (array_key_exists('active_assignments_count', $this->attributes)) {
            return $this->active_assignments_count;
        }
        return $this->activeAssignments()->count();
    }

    public function availableSeats(): int
    {
        return max(0, $this->total_seats - $this->usedSeats());
    }

    public function hasAvailableSeats(): bool
    {
        return $this->availableSeats() > 0;
    }

    // -------------------------------------------------------------------------
    // Relaciones
    // -------------------------------------------------------------------------
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LicenseAssignment::class);
    }

    public function activeAssignments(): HasMany
    {
        return $this->hasMany(LicenseAssignment::class)->whereNull('released_at');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}

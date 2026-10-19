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
        'billing_cycle',
        'auto_renew',
        'purchase_date',
        'expiry_date',
        'purchase_price',
        'currency',
        'supplier_id',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'purchase_date'  => 'date',
        'expiry_date'    => 'date',
        'purchase_price' => 'decimal:2',
        'total_seats'    => 'integer',
        'auto_renew'     => 'boolean',
    ];

    public const TYPES = [
        'perpetual'    => 'Perpetua',
        'subscription' => 'Suscripción',
        'per_device'   => 'Por dispositivo',
        'per_user'     => 'Por usuario / sede',
        'concurrent'   => 'Concurrente',
    ];

    public const BILLING_CYCLES = [
        'monthly' => 'Mensual',
        'annual'  => 'Anual',
    ];

    public function isSubscription(): bool
    {
        return $this->license_type === 'subscription';
    }

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

    // Subscriptions can have no seat cap at all (e.g. a flat-fee SaaS tool
    // nobody is individually assigned to) — total_seats is nullable precisely
    // for that case, so null means "unlimited", not "zero".
    public function hasSeatLimit(): bool
    {
        return ! is_null($this->total_seats);
    }

    public function availableSeats(): ?int
    {
        if (! $this->hasSeatLimit()) {
            return null;
        }

        return max(0, $this->total_seats - $this->usedSeats());
    }

    public function hasAvailableSeats(): bool
    {
        return ! $this->hasSeatLimit() || $this->availableSeats() > 0;
    }

    public function getSeatsLabel(): string
    {
        return $this->hasSeatLimit()
            ? $this->usedSeats() . ' / ' . $this->total_seats
            : $this->usedSeats() . ' (sin límite)';
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

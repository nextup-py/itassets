<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ServiceContract extends Model
{
    use HasFactory, Blameable, LogsActivity;

    protected $fillable = [
        'name',
        'contract_type',
        'supplier_id',
        'start_date',
        'expiry_date',
        'cost',
        'currency',
        'billing_cycle',
        'auto_renew',
        'sla',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'expiry_date' => 'date',
        'cost'        => 'decimal:2',
        'auto_renew'  => 'boolean',
    ];

    public const TYPES = [
        'maintenance'       => 'Mantenimiento',
        'support'           => 'Soporte',
        'extended_warranty' => 'Garantía extendida',
        'consulting'        => 'Consultoría',
    ];

    public const BILLING_CYCLES = [
        'one_time' => 'Pago único',
        'monthly'  => 'Mensual',
        'annual'   => 'Anual',
    ];

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->contract_type] ?? $this->contract_type;
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'service_contract_asset')->withTimestamps();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}

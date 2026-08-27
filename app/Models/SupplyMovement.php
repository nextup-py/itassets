<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SupplyMovement extends Model
{
    use Blameable, HasFactory, LogsActivity;

    protected $fillable = [
        'supply_id',
        'type',
        'quantity',
        'employee_id',
        'department_id',
        'received_supply_id',
        'performed_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'performed_at' => 'date',
        'quantity' => 'integer',
    ];

    public const TYPES = [
        'entrega' => 'Entrega',
        'reemplazo' => 'Reemplazo',
    ];

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }

    public function receivedSupply(): BelongsTo
    {
        return $this->belongsTo(Supply::class, 'received_supply_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function getRecipientLabel(): string
    {
        return $this->employee?->name ?? $this->department?->name ?? '—';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}

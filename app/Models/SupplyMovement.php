<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SupplyMovement extends Model
{
    use Blameable, HasFactory, LogsActivity;

    // Mirrors Assignment::booted()'s saving hook: the UI only ever submits one
    // of employee_id/department_id (via its recipient_type Radio), but nothing
    // stopped another creation path (an import, a future API) from setting
    // both or neither.
    protected static function booted(): void
    {
        static::saving(function (SupplyMovement $movement) {
            if (is_null($movement->employee_id) === is_null($movement->department_id)) {
                throw new InvalidArgumentException('A supply movement must have exactly one of employee_id or department_id.');
            }
        });
    }

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

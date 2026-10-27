<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LicenseAssignment extends Model
{
    use HasFactory, Blameable, LogsActivity;

    protected $fillable = [
        'license_id',
        'asset_id',
        'employee_id',
        'assigned_at',
        'released_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'assigned_at' => 'date',
        'released_at' => 'date',
    ];

    // -------------------------------------------------------------------------
    // Bloqueo cuando no hay seats disponibles
    // -------------------------------------------------------------------------
    protected static function booted(): void
    {
        static::creating(function (LicenseAssignment $assignment) {
            if (blank($assignment->asset_id) && blank($assignment->employee_id)) {
                throw ValidationException::withMessages([
                    'asset_id' => 'Debe asignarse a un Activo o a un Empleado.',
                ]);
            }

            $license = License::find($assignment->license_id);

            if ($license && ! $license->hasAvailableSeats()) {
                throw ValidationException::withMessages([
                    'license_id' => "La licencia \"{$license->product_name}\" no tiene seats disponibles ({$license->usedSeats()}/{$license->total_seats} en uso).",
                ]);
            }

            $duplicate = static::query()
                ->where('license_id', $assignment->license_id)
                ->whereNull('released_at')
                ->when($assignment->asset_id, fn ($q) => $q->where('asset_id', $assignment->asset_id))
                ->when($assignment->employee_id, fn ($q) => $q->where('employee_id', $assignment->employee_id))
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'asset_id' => 'Ya existe una asignación activa de esta licencia para el mismo activo o empleado.',
                ]);
            }
        });
    }

    /**
     * Wraps new-record creation in a transaction holding a row lock on the
     * parent License, so two concurrent assignments near the seat limit
     * can't both pass the hasAvailableSeats() check before either commits.
     */
    public function save(array $options = [])
    {
        if ($this->exists || blank($this->license_id)) {
            return parent::save($options);
        }

        return DB::transaction(function () use ($options) {
            License::whereKey($this->license_id)->lockForUpdate()->first();

            return parent::save($options);
        });
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
    public function isActive(): bool
    {
        return is_null($this->released_at);
    }

    public function assigneeName(): string
    {
        if ($this->asset) {
            return "[{$this->asset->asset_tag}] {$this->asset->name}";
        }
        if ($this->employee) {
            return $this->employee->name;
        }
        return '—';
    }

    // -------------------------------------------------------------------------
    // Relaciones
    // -------------------------------------------------------------------------
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}

<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
            $license = License::find($assignment->license_id);

            if ($license && ! $license->hasAvailableSeats()) {
                throw ValidationException::withMessages([
                    'license_id' => "La licencia \"{$license->product_name}\" no tiene seats disponibles ({$license->usedSeats()}/{$license->total_seats} en uso).",
                ]);
            }
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

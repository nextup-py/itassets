<?php

namespace App\Models;

use App\Notifications\AssetAssignmentNotification;
use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Assignment extends Model
{
    use HasFactory, Blameable, LogsActivity;

    protected $fillable = [
        'employee_id',
        'department_id',
        'assigned_by',
        'assigned_at',
        'returned_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'assigned_at' => 'date',
        'returned_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (Assignment $assignment) {
            if (empty($assignment->employee_id) && empty($assignment->department_id)) {
                throw new \InvalidArgumentException('Una asignación debe tener un empleado o un departamento.');
            }

            if (! empty($assignment->employee_id) && ! empty($assignment->department_id)) {
                throw new \InvalidArgumentException('Una asignación no puede tener empleado y departamento a la vez.');
            }

            // Only re-validate when employee_id is actually being set (a new
            // assignment, or a recipient change on an existing one) — an
            // untouched assignment whose employee later became inactive must
            // still be editable (e.g. to register its return).
            if (! empty($assignment->employee_id) && (! $assignment->exists || $assignment->isDirty('employee_id'))) {
                $isActive = Employee::whereKey($assignment->employee_id)->where('is_active', true)->exists();

                if (! $isActive) {
                    throw new \InvalidArgumentException('No se puede asignar un activo a un empleado inactivo.');
                }
            }
        });

        // assignment_asset.assignment_id is cascadeOnDelete() at the DB level,
        // so deleting an Assignment the normal way never fires AssignmentAsset's
        // own deleted hook (the one that reverts Asset.status to 'available') —
        // same gotcha as MaintenanceRecord/MaintenanceRecordSupply. Detaching
        // first (the relation's using() makes detach() delete each pivot row
        // individually, which does fire that event) restores every asset's
        // status before the parent row, and its now-empty cascade, go.
        static::deleting(function (Assignment $assignment) {
            $assignment->assets()->detach();
        });

        static::updated(function (Assignment $assignment) {
            if ($assignment->wasChanged('returned_at') && ! is_null($assignment->returned_at)) {
                $managers = User::managers()->get();

                foreach ($assignment->assets as $asset) {
                    $asset->update(['status' => 'available']);

                    foreach ($managers as $manager) {
                        (new AssetAssignmentNotification($asset, 'returned', $assignment->recipientEmployee()))->sendToManager($manager);
                    }
                }
            }
        });
    }

    /**
     * Normalizes raw form data so exactly one of employee_id/department_id
     * survives, and strips the UI-only recipient_type radio.
     */
    public static function normalizeRecipientData(array $data): array
    {
        $type = $data['recipient_type'] ?? (empty($data['department_id']) ? 'employee' : 'department');

        if ($type === 'department') {
            $data['employee_id'] = null;
        } else {
            $data['department_id'] = null;
        }

        unset($data['recipient_type']);

        return $data;
    }

    public function recipientEmployee(): ?Employee
    {
        if ($this->employee) {
            return $this->employee;
        }

        $contact = $this->department?->contactEmployee;

        // An inactive contact (e.g. no longer with the company) is treated
        // as if the department had none — notifyEmployee()/the assignment
        // PDF skip it instead of mailing a departed employee indefinitely.
        return $contact?->is_active ? $contact : null;
    }

    public function getRecipientLabel(): string
    {
        return $this->employee?->name ?? $this->department?->name ?? '—';
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereNull('returned_at');
    }

    public function scopeReturned(Builder $query): void
    {
        $query->whereNotNull('returned_at');
    }

    public function isActive(): bool
    {
        return is_null($this->returned_at);
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'assignment_asset')
            ->using(AssignmentAsset::class)
            ->withPivot(['charger_serial', 'ticket_number', 'assigned_at', 'notes'])
            ->withTimestamps();
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}

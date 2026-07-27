<?php

namespace App\Models;

use App\Notifications\AssetAssignmentNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AssignmentAsset extends Pivot
{
    use HasFactory;

    protected $table = 'assignment_asset';

    protected $fillable = [
        'assignment_id',
        'asset_id',
        'charger_serial',
        'ticket_number',
        'assigned_at',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::created(function (AssignmentAsset $pivot) {
            if ($pivot->asset) {
                $pivot->asset->update(['status' => 'assigned']);

                foreach (User::managers()->get() as $manager) {
                    (new AssetAssignmentNotification(
                        $pivot->asset,
                        'assigned',
                        $pivot->assignment?->employee,
                    ))->sendToManager($manager);
                }
            }
        });

        static::deleted(function (AssignmentAsset $pivot) {
            if ($pivot->asset) {
                $pivot->asset->update(['status' => 'available']);
            }
        });
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}

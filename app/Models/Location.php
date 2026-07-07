<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Location extends Model
{
    use HasFactory, Blameable, LogsActivity;

    protected $fillable = [
        'name',
        'building',
        'floor',
        'room',
        'notes',
        'created_by',
        'updated_by',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}

<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Supplier extends Model
{
    use HasFactory, Blameable, LogsActivity;

    protected $fillable = [
        'name',
        'contact_name',
        'email',
        'phone',
        'website',
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

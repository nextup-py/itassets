<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AssetCategory extends Model
{
    use HasFactory, Blameable, LogsActivity;

    protected $fillable = [
        'name',
        'type',
        'description',
        'created_by',
        'updated_by',
    ];

    public const TYPES = [
        'hardware'       => 'Hardware',
        'software'       => 'Software / Licencias',
        'peripheral'     => 'Periféricos',
        'infrastructure' => 'Infraestructura',
        'mobile'         => 'Dispositivos Móviles',
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

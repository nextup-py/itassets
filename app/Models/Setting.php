<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Setting extends Model
{
    use Blameable, LogsActivity;

    protected $fillable = ['key', 'value', 'created_by', 'updated_by'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        // `value` is a nullable column (database/migrations/*_create_settings_table.php),
        // so a row can exist with a literal NULL value — not just be absent entirely. A row
        // like that must fall back to $default exactly like a missing row does, or every
        // caller passing a sane default (most of them) silently gets null back forever once
        // one bad write happens, with no error until something downstream chokes on it (this
        // is what broke `FilamentTimezone::set()`/the scheduler's `->timezone()` closures in
        // production: a stale NULL 'timezone' row fed `Carbon::setTimezone(null)`).
        if (! $setting || $setting->value === null) {
            return $default;
        }

        $value = $setting->value;

        if (is_string($value) && (str_starts_with($value, '{') || str_starts_with($value, '['))) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value]
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}

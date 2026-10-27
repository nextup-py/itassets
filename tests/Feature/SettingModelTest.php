<?php

use App\Models\Setting;

it('returns the default when the key does not exist at all', function () {
    expect(Setting::get('does_not_exist', 'fallback'))->toBe('fallback');
});

it('returns the stored value when one is set', function () {
    Setting::set('timezone', 'America/Asuncion');

    expect(Setting::get('timezone', 'UTC'))->toBe('America/Asuncion');
});

it('returns the default, not a stored literal null, when the row exists but value is null', function () {
    // The `value` column is nullable (database/migrations/*_create_settings_table.php), so a
    // row can exist with value === null without having ever gone through Setting::set() at
    // all (e.g. a raw insert, a factory, or any future code path). Every caller passing a
    // default expects to get it back in that case, exactly as if the key were unset — this
    // regressed in production (a stale null 'timezone' row fed Carbon::setTimezone(null)).
    Setting::query()->create(['key' => 'timezone', 'value' => null]);

    expect(Setting::get('timezone', 'UTC'))->toBe('UTC');
});

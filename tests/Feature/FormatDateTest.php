<?php

use App\Models\Setting;

it('defaults to d/m/Y when no setting exists', function () {
    expect(current_date_format())->toBe('d/m/Y');
});

it('returns the configured date_format setting', function () {
    Setting::set('date_format', 'Y-m-d');

    expect(current_date_format())->toBe('Y-m-d');
});

it('appends a time component to the date format for current_datetime_format', function () {
    Setting::set('date_format', 'm/d/Y');

    expect(current_datetime_format())->toBe('m/d/Y H:i');
});

<?php

use App\Models\Setting;

it('returns dash for null amount', function () {
    expect(format_currency(null))->toBe('—');
});

it('formats in base currency when no display currency is set', function () {
    Setting::set('base_currency', 'PYG');
    Setting::set('display_locale', 'es_PY');

    expect(format_currency(0))->toBe('Gs. 0');
    expect(format_currency(6500))->toBe('Gs. 6.500');
    expect(format_currency(6500000))->toBe('Gs. 6.500.000');
});

it('does not convert when display_currency equals base_currency', function () {
    Setting::set('base_currency', 'PYG');
    Setting::set('display_currency', 'PYG');
    Setting::set('exchange_rate', 9999);
    Setting::set('display_locale', 'es_PY');

    expect(format_currency(6500))->toBe('Gs. 6.500');
});

it('converts to display_currency using exchange_rate when they differ', function () {
    Setting::set('base_currency', 'PYG');
    Setting::set('display_currency', 'USD');
    Setting::set('exchange_rate', 0.00015);
    Setting::set('display_locale', 'en_US');

    expect(format_currency(6500))->toBe('$0.98');
});

it('defaults to USD/en_US when no settings exist', function () {
    expect(format_currency(1))->toBe('$1.00');
    expect(format_currency(99.99))->toBe('$99.99');
});

it('formats in a non-Paraguay base currency and locale', function () {
    Setting::set('base_currency', 'EUR');
    Setting::set('display_locale', 'de_DE');

    expect(format_currency(6500))->toBe("6.500,00\u{a0}€");
});

it('converts between two non-USD, non-PYG currencies', function () {
    Setting::set('base_currency', 'EUR');
    Setting::set('display_currency', 'GBP');
    Setting::set('exchange_rate', 0.86);
    Setting::set('display_locale', 'en_GB');

    expect(format_currency(100))->toBe('£86.00');
});

<?php

use App\Models\Setting;

if (! function_exists('format_currency')) {
    function format_currency(?float $amount): string
    {
        if (is_null($amount)) {
            return '—';
        }

        $baseCurrency = Setting::get('base_currency', 'USD');
        $displayCurrency = Setting::get('display_currency') ?: $baseCurrency;
        $rate = $displayCurrency === $baseCurrency ? 1 : (float) Setting::get('exchange_rate', 1);
        $locale = Setting::get('display_locale', 'en_US');

        $converted = round(round($amount * $rate, 6), 2, PHP_ROUND_HALF_UP);
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $formatted = $formatter->formatCurrency($converted, $displayCurrency);

        return str_replace("\xC2\xA0", ' ', $formatted);
    }
}

if (! function_exists('current_date_format')) {
    function current_date_format(): string
    {
        return Setting::get('date_format', 'd/m/Y');
    }
}

if (! function_exists('current_datetime_format')) {
    function current_datetime_format(): string
    {
        return current_date_format() . ' H:i';
    }
}

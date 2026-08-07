<?php

use App\Models\Setting;

if (! function_exists('format_currency')) {
    /**
     * $currency is the currency the amount is actually denominated in (e.g. an
     * Asset/License's own `currency` column). Null, or equal to base_currency,
     * means "amount is in the installation's base currency" — the existing
     * base_currency -> display_currency conversion applies. Any other value
     * means the record carries its own currency, shown as-is (no conversion —
     * there's no exchange rate for arbitrary currency pairs, only for
     * base_currency <-> display_currency).
     */
    function format_currency(?float $amount, ?string $currency = null): string
    {
        if (is_null($amount)) {
            return '—';
        }

        $baseCurrency = Setting::get('base_currency', 'USD');
        $locale = Setting::get('display_locale', 'en_US');

        if ($currency && $currency !== $baseCurrency) {
            $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
            $formatted = $formatter->formatCurrency(round($amount, 2, PHP_ROUND_HALF_UP), $currency);

            return str_replace("\xC2\xA0", ' ', $formatted);
        }

        $displayCurrency = Setting::get('display_currency') ?: $baseCurrency;
        $rate = $displayCurrency === $baseCurrency ? 1 : (float) Setting::get('exchange_rate', 1);

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

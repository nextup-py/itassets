<?php

use App\Models\Setting;

if (! function_exists('format_gs')) {
    function format_gs(?float $amount): string
    {
        if (is_null($amount)) {
            return '—';
        }

        $rate = (float) Setting::get('exchange_rate_usd_pyg', 6500);

        return 'Gs. ' . number_format($amount * $rate, 0, ',', '.');
    }
}

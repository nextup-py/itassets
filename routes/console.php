<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Schedule;

Schedule::command('notifications:check')
    ->dailyAt('08:00')
    ->timezone(fn () => Setting::get('timezone', config('app.timezone')));

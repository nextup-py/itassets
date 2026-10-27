<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('notifications:check')
    ->dailyAt('08:00')
    ->timezone(fn () => Setting::get('timezone', config('app.timezone')));

// Unattended backup: dump the DB + app files, then prune old copies per
// config/backup.php's retention strategy, and verify a healthy backup exists.
// Runs at a quiet hour so it never competes with daytime usage.
Schedule::command('backup:run')
    ->dailyAt('03:00')
    ->timezone(fn () => Setting::get('timezone', config('app.timezone')))
    ->onSuccess(fn () => Artisan::call('backup:clean'));

Schedule::command('backup:monitor')
    ->dailyAt('03:30')
    ->timezone(fn () => Setting::get('timezone', config('app.timezone')));

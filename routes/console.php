<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

// Illuminate\Console\Scheduling\Event::timezone() (unlike Filament's own
// ->multiFactorAuthentication()-style closure setters) stores whatever it's
// given as-is and never evaluates a Closure -- passing one here used to feed
// a raw Closure straight into Carbon::setTimezone(), throwing a TypeError on
// every single `schedule:run` tick. A console command is a fresh process on
// every invocation, so this file (and this value) is re-evaluated fresh on
// each `schedule:run` anyway -- there's no "process boot" staleness to guard
// against the way there is for a long-lived Filament web worker, so a plain
// eager value is correct here. This file loads on every artisan boot, not
// just schedule:run, so the lookup is wrapped in a try/catch rather than
// just a Schema::hasTable() check (which itself needs a DB connection) --
// a command run before the DB is reachable at all (fresh server, migrations
// not yet run, local dev without a DB up) must not crash every single
// artisan invocation just because this file always loads.
try {
    $timezone = Schema::hasTable('settings')
        ? Setting::get('timezone', config('app.timezone'))
        : config('app.timezone');
} catch (\Throwable) {
    $timezone = config('app.timezone');
}

Schedule::command('notifications:check')
    ->dailyAt('08:00')
    ->timezone($timezone);

// Unattended backup: dump the DB + app files, then prune old copies per
// config/backup.php's retention strategy, and verify a healthy backup exists.
// Runs at a quiet hour so it never competes with daytime usage.
Schedule::command('backup:run')
    ->dailyAt('03:00')
    ->timezone($timezone)
    ->onSuccess(fn () => Artisan::call('backup:clean'));

Schedule::command('backup:monitor')
    ->dailyAt('03:30')
    ->timezone($timezone);

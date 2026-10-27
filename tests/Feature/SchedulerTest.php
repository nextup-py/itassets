<?php

use Illuminate\Console\Scheduling\Schedule;

it('registers the scheduled commands with a resolved timezone, not a raw Closure', function () {
    $events = app(Schedule::class)->events();

    expect($events)->toHaveCount(3);

    foreach ($events as $event) {
        expect($event->timezone)->not->toBeInstanceOf(Closure::class);
    }
});

it('does not throw when the scheduler checks whether commands are due', function () {
    // Regression test: Illuminate\Console\Scheduling\Event::timezone() never
    // evaluates a Closure, it just stores whatever it's given -- a Closure
    // passed there used to be fed straight into Carbon::setTimezone() here,
    // throwing a TypeError on every single `schedule:run` tick in production.
    foreach (app(Schedule::class)->events() as $event) {
        expect(fn () => $event->isDue(app()))->not->toThrow(Throwable::class);
    }
});

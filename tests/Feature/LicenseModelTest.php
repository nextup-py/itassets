<?php

use App\Models\Employee;
use App\Models\License;
use App\Models\LicenseAssignment;

beforeEach(function () {
    loginAsAdmin();
});

it('computes used seats correctly', function () {
    $license = License::factory()->create(['total_seats' => 10]);
    LicenseAssignment::factory()->count(3)->create(['license_id' => $license->id]);

    expect($license->usedSeats())->toBe(3);
});

it('computes available seats correctly', function () {
    $license = License::factory()->create(['total_seats' => 10]);
    LicenseAssignment::factory()->count(3)->create(['license_id' => $license->id]);

    expect($license->availableSeats())->toBe(7);
});

it('has available seats when none assigned', function () {
    $license = License::factory()->create(['total_seats' => 5]);
    LicenseAssignment::factory()->create(['license_id' => $license->id, 'released_at' => now()]);

    expect($license->availableSeats())->toBe(5);
});

it('has no available seats when fully assigned', function () {
    $license = License::factory()->create(['total_seats' => 2]);
    LicenseAssignment::factory()->count(2)->create(['license_id' => $license->id]);

    expect($license->hasAvailableSeats())->toBeFalse();
});

it('has available seats when not fully assigned', function () {
    $license = License::factory()->create(['total_seats' => 5]);
    LicenseAssignment::factory()->count(3)->create(['license_id' => $license->id]);

    expect($license->hasAvailableSeats())->toBeTrue();
});

it('has license types defined', function () {
    expect(License::TYPES)->toHaveKeys(['perpetual', 'subscription', 'per_device', 'per_user', 'concurrent']);
});

it('has billing cycles defined', function () {
    expect(License::BILLING_CYCLES)->toHaveKeys(['monthly', 'annual']);
});

it('treats a null total_seats as unlimited, not zero', function () {
    $license = License::factory()->subscription()->create(['total_seats' => null]);
    LicenseAssignment::factory()->count(5)->create(['license_id' => $license->id]);

    expect($license->hasSeatLimit())->toBeFalse()
        ->and($license->availableSeats())->toBeNull()
        ->and($license->hasAvailableSeats())->toBeTrue();
});

it('allows an unlimited-seats subscription to be assigned past any fixed count', function () {
    $license = License::factory()->subscription()->create(['total_seats' => null]);

    $assignment = LicenseAssignment::create([
        'license_id' => $license->id,
        'employee_id' => Employee::factory()->create()->id,
        'assigned_at' => now(),
    ]);

    expect($assignment->exists)->toBeTrue();
});

it('shows "sin límite" in the seats label when total_seats is null', function () {
    $license = License::factory()->subscription()->create(['total_seats' => null]);

    expect($license->getSeatsLabel())->toBe('0 (sin límite)');
});

<?php

use App\Models\Asset;
use App\Models\License;
use App\Models\MaintenanceRecord;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    loginAsAdmin();
});

it('creates notifications for expiring warranties', function () {
    Asset::factory()->create([
        'warranty_expiry_date' => now()->addDays(30),
    ]);

    $this->artisan('notifications:check')->assertSuccessful();

    $admin = User::first();
    expect($admin->notifications()->count())->toBeGreaterThanOrEqual(1);
});

it('creates notifications for expired warranties', function () {
    Asset::factory()->create([
        'warranty_expiry_date' => now()->subDays(10),
    ]);

    $this->artisan('notifications:check')->assertSuccessful();

    $admin = User::first();
    expect($admin->notifications()->count())->toBeGreaterThanOrEqual(1);
});

it('creates notifications for expiring licenses', function () {
    License::factory()->create([
        'expiry_date' => now()->addDays(30),
    ]);

    $this->artisan('notifications:check')->assertSuccessful();

    $admin = User::first();
    expect($admin->notifications()->count())->toBeGreaterThanOrEqual(1);
});

it('creates notifications for prolonged maintenance', function () {
    MaintenanceRecord::factory()->inProgress()->create([
        'started_at' => now()->subDays(10),
    ]);

    $this->artisan('notifications:check')->assertSuccessful();

    $admin = User::first();
    expect($admin->notifications()->count())->toBeGreaterThanOrEqual(1);
});

it('does not create notifications when no expirations exist', function () {
    Asset::factory()->create([
        'warranty_expiry_date' => now()->addYear(),
    ]);

    $this->artisan('notifications:check')->assertSuccessful();

    $admin = User::first();
    expect($admin->notifications()->count())->toBe(0);
});

it('sends notifications to Admin and Editor roles', function () {
    createRolesAndPermissions();
    $admin = User::factory()->admin()->create(['email' => 'admin2@test.com']);
    $editor = User::factory()->editor()->create(['email' => 'editor@test.com']);
    $viewer = User::factory()->viewer()->create(['email' => 'viewer@test.com']);

    Asset::factory()->create([
        'warranty_expiry_date' => now()->addDays(30),
    ]);

    $this->artisan('notifications:check')->assertSuccessful();

    expect($admin->notifications()->count())->toBeGreaterThanOrEqual(1);
    expect($editor->notifications()->count())->toBeGreaterThanOrEqual(1);
    expect($viewer->notifications()->count())->toBe(0);
});

it('does not duplicate warranty notifications when the command runs twice in the same day', function () {
    Asset::factory()->create(['warranty_expiry_date' => now()->addDays(30)]);

    $this->artisan('notifications:check')->assertSuccessful();
    $admin = User::first();
    $countAfterFirstRun = $admin->notifications()->count();
    expect($countAfterFirstRun)->toBeGreaterThanOrEqual(1);

    $this->artisan('notifications:check')->assertSuccessful();

    expect($admin->notifications()->count())->toBe($countAfterFirstRun);
});

it('re-sends the warranty notification after the cooldown period elapses', function () {
    Asset::factory()->create(['warranty_expiry_date' => now()->addDays(30)]);

    $this->artisan('notifications:check')->assertSuccessful();
    $admin = User::first();
    $countAfterFirstRun = $admin->notifications()->count();

    $this->travel(8)->days();
    $this->artisan('notifications:check')->assertSuccessful();

    expect($admin->notifications()->count())->toBe($countAfterFirstRun * 2);
});

it('does not duplicate license notifications when the command runs twice in the same day', function () {
    License::factory()->create(['expiry_date' => now()->addDays(30)]);

    $this->artisan('notifications:check')->assertSuccessful();
    $admin = User::first();
    $countAfterFirstRun = $admin->notifications()->count();
    expect($countAfterFirstRun)->toBeGreaterThanOrEqual(1);

    $this->artisan('notifications:check')->assertSuccessful();

    expect($admin->notifications()->count())->toBe($countAfterFirstRun);
});

it('does not duplicate maintenance notifications when the command runs twice in the same day', function () {
    MaintenanceRecord::factory()->inProgress()->create(['started_at' => now()->subDays(10)]);

    $this->artisan('notifications:check')->assertSuccessful();
    $admin = User::first();
    $countAfterFirstRun = $admin->notifications()->count();
    expect($countAfterFirstRun)->toBeGreaterThanOrEqual(1);

    $this->artisan('notifications:check')->assertSuccessful();

    expect($admin->notifications()->count())->toBe($countAfterFirstRun);
});

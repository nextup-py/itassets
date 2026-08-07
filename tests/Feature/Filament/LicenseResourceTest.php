<?php

use App\Filament\Resources\Licenses\Pages\CreateLicense;
use App\Filament\Resources\Licenses\Pages\ListLicenses;
use App\Models\Asset;
use App\Models\Employee;
use App\Models\License;
use App\Models\LicenseAssignment;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('creates a license with its own currency', function () {
    Livewire::test(CreateLicense::class)
        ->fillForm([
            'product_name' => 'Adobe Creative Cloud',
            'license_type' => 'subscription',
            'total_seats' => 5,
            'purchase_price' => 600,
            'currency' => 'USD',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(License::where('product_name', 'Adobe Creative Cloud')->first()->currency)->toBe('USD');
});

it('creates a license without a currency (optional field)', function () {
    Livewire::test(CreateLicense::class)
        ->fillForm([
            'product_name' => 'Slack Enterprise',
            'license_type' => 'subscription',
            'total_seats' => 5,
            'currency' => '',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(License::where('product_name', 'Slack Enterprise')->first()->currency)->toBeNull();
});

it('rejects a license currency that is not a 3-letter code', function () {
    Livewire::test(CreateLicense::class)
        ->fillForm([
            'product_name' => 'Slack Enterprise',
            'license_type' => 'subscription',
            'total_seats' => 5,
            'currency' => 'US$',
        ])
        ->call('create')
        ->assertHasFormErrors(['currency' => 'regex']);
});

it('rejects lowering total_seats below currently used seats', function () {
    $license = License::factory()->create(['total_seats' => 5]);
    LicenseAssignment::factory()->count(3)->create(['license_id' => $license->id]);

    expect($license->usedSeats())->toBe(3);

    Livewire::test(\App\Filament\Resources\Licenses\Pages\EditLicense::class, ['record' => $license->id])
        ->fillForm(['total_seats' => 2])
        ->call('save')
        ->assertHasFormErrors(['total_seats']);
});

it('allows lowering total_seats to a value still covering used seats', function () {
    $license = License::factory()->create(['total_seats' => 5]);
    LicenseAssignment::factory()->count(3)->create(['license_id' => $license->id]);

    Livewire::test(\App\Filament\Resources\Licenses\Pages\EditLicense::class, ['record' => $license->id])
        ->fillForm(['total_seats' => 3])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($license->fresh()->total_seats)->toBe(3);
});

it('blocks assigning the same license twice to the same asset', function () {
    $license = License::factory()->create(['total_seats' => 5]);
    $asset = Asset::factory()->create();

    LicenseAssignment::factory()->create(['license_id' => $license->id, 'asset_id' => $asset->id]);

    expect(fn () => LicenseAssignment::create([
        'license_id' => $license->id,
        'asset_id' => $asset->id,
        'assigned_at' => now(),
    ]))->toThrow(ValidationException::class);
});

it('blocks assigning the same license twice to the same employee', function () {
    $license = License::factory()->create(['total_seats' => 5]);
    $employee = Employee::factory()->create();

    LicenseAssignment::factory()->toEmployee()->create(['license_id' => $license->id, 'employee_id' => $employee->id]);

    expect(fn () => LicenseAssignment::create([
        'license_id' => $license->id,
        'employee_id' => $employee->id,
        'assigned_at' => now(),
    ]))->toThrow(ValidationException::class);
});

it('allows re-assigning the same license to an asset once the prior assignment is released', function () {
    $license = License::factory()->create(['total_seats' => 5]);
    $asset = Asset::factory()->create();

    LicenseAssignment::factory()->released()->create(['license_id' => $license->id, 'asset_id' => $asset->id]);

    $assignment = LicenseAssignment::create([
        'license_id' => $license->id,
        'asset_id' => $asset->id,
        'assigned_at' => now(),
    ]);

    expect($assignment->exists)->toBeTrue();
});

it('filters licenses by expired status', function () {
    $expired = License::factory()->create(['expiry_date' => now()->subDays(5)]);
    $expiringSoon = License::factory()->create(['expiry_date' => now()->addDays(30)]);
    $valid = License::factory()->create(['expiry_date' => now()->addDays(120)]);
    $noExpiry = License::factory()->create(['expiry_date' => null]);

    Livewire::test(ListLicenses::class)
        ->filterTable('expiry_status', ['value' => 'expired'])
        ->assertCanSeeTableRecords([$expired])
        ->assertCanNotSeeTableRecords([$expiringSoon, $valid, $noExpiry]);
});

it('filters licenses by expiring soon status', function () {
    $expired = License::factory()->create(['expiry_date' => now()->subDays(5)]);
    $expiringSoon = License::factory()->create(['expiry_date' => now()->addDays(30)]);
    $valid = License::factory()->create(['expiry_date' => now()->addDays(120)]);
    $noExpiry = License::factory()->create(['expiry_date' => null]);

    Livewire::test(ListLicenses::class)
        ->filterTable('expiry_status', ['value' => 'expiring_soon'])
        ->assertCanSeeTableRecords([$expiringSoon])
        ->assertCanNotSeeTableRecords([$expired, $valid, $noExpiry]);
});

it('filters licenses by valid status', function () {
    $expired = License::factory()->create(['expiry_date' => now()->subDays(5)]);
    $expiringSoon = License::factory()->create(['expiry_date' => now()->addDays(30)]);
    $valid = License::factory()->create(['expiry_date' => now()->addDays(120)]);
    $noExpiry = License::factory()->create(['expiry_date' => null]);

    Livewire::test(ListLicenses::class)
        ->filterTable('expiry_status', ['value' => 'valid'])
        ->assertCanSeeTableRecords([$valid, $noExpiry])
        ->assertCanNotSeeTableRecords([$expired, $expiringSoon]);
});

<?php

use App\Filament\Resources\Assets\Pages\ListAssets;
use App\Filament\Resources\Assignments\Pages\ListAssignments;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Filament\Resources\Licenses\Pages\ListLicenses;
use App\Filament\Resources\MaintenanceRecords\Pages\ListMaintenanceRecords;
use App\Filament\Resources\Supplies\Pages\ListSupplies;
use App\Models\Asset;
use App\Models\Assignment;
use App\Models\Employee;
use App\Models\License;
use App\Models\MaintenanceRecord;
use App\Models\Supply;
use Livewire\Livewire;

beforeEach(function () {
    createRolesAndPermissions();
});

// Assets: changeStatus

it('changes an asset status from the table row action', function () {
    loginAsAdmin();
    $asset = Asset::factory()->available()->create();

    Livewire::test(ListAssets::class)
        ->callTableAction('changeStatus', $asset, data: ['status' => 'retired']);

    expect($asset->refresh()->status)->toBe('retired');
});

it('hides the changeStatus action from viewers', function () {
    loginAsViewer();
    $asset = Asset::factory()->available()->create();

    Livewire::test(ListAssets::class)
        ->assertTableActionHidden('changeStatus', $asset);
});

// Assignments: returnNow

it('returns an active assignment from the table row action', function () {
    loginAsAdmin();
    $assignment = Assignment::factory()->create(['returned_at' => null]);

    Livewire::test(ListAssignments::class)
        ->callTableAction('returnNow', $assignment);

    expect($assignment->refresh()->returned_at)->not->toBeNull();
});

it('hides returnNow on an already-returned assignment', function () {
    loginAsAdmin();
    $assignment = Assignment::factory()->returned()->create();

    Livewire::test(ListAssignments::class)
        ->assertTableActionHidden('returnNow', $assignment);
});

it('hides the returnNow action from viewers', function () {
    loginAsViewer();
    $assignment = Assignment::factory()->create(['returned_at' => null]);

    Livewire::test(ListAssignments::class)
        ->assertTableActionHidden('returnNow', $assignment);
});

// Employees: toggleActive

it('toggles an employee active status from the table row action', function () {
    loginAsAdmin();
    $employee = Employee::factory()->create(['is_active' => true]);

    Livewire::test(ListEmployees::class)
        ->callTableAction('toggleActive', $employee);

    expect($employee->refresh()->is_active)->toBeFalse();
});

it('hides the toggleActive action from viewers', function () {
    loginAsViewer();
    $employee = Employee::factory()->create();

    Livewire::test(ListEmployees::class)
        ->assertTableActionHidden('toggleActive', $employee);
});

// Licenses: renew

it('renews a license expiring soon from the table row action', function () {
    loginAsAdmin();
    $license = License::factory()->create([
        'expiry_date' => now()->addDays(10),
        'total_seats' => 5,
    ]);

    Livewire::test(ListLicenses::class)
        ->callTableAction('renew', $license, data: [
            'expiry_date' => now()->addYear()->toDateString(),
            'total_seats' => 10,
        ]);

    expect($license->refresh()->total_seats)->toBe(10)
        ->and($license->expiry_date->isFuture())->toBeTrue();
});

it('hides renew for a license not close to expiry', function () {
    loginAsAdmin();
    $license = License::factory()->create(['expiry_date' => now()->addYear()]);

    Livewire::test(ListLicenses::class)
        ->assertTableActionHidden('renew', $license);
});

it('hides the renew action from viewers', function () {
    loginAsViewer();
    $license = License::factory()->create(['expiry_date' => now()->addDays(10)]);

    Livewire::test(ListLicenses::class)
        ->assertTableActionHidden('renew', $license);
});

// MaintenanceRecords: complete

it('marks a maintenance record as completed from the table row action', function () {
    loginAsAdmin();
    $record = MaintenanceRecord::factory()->inProgress()->create();

    Livewire::test(ListMaintenanceRecords::class)
        ->callTableAction('complete', $record);

    expect($record->refresh()->status)->toBe('completed')
        ->and($record->completed_at)->not->toBeNull();
});

it('hides complete on an already-completed maintenance record', function () {
    loginAsAdmin();
    $record = MaintenanceRecord::factory()->completed()->create();

    Livewire::test(ListMaintenanceRecords::class)
        ->assertTableActionHidden('complete', $record);
});

it('hides the complete action from viewers', function () {
    loginAsViewer();
    $record = MaintenanceRecord::factory()->inProgress()->create();

    Livewire::test(ListMaintenanceRecords::class)
        ->assertTableActionHidden('complete', $record);
});

// Supplies: manualAdjustment

it('registers a manual adjustment from the table row action', function () {
    loginAsAdmin();
    $supply = Supply::factory()->create(['quantity_available' => 10]);

    Livewire::test(ListSupplies::class)
        ->callTableAction('manualAdjustment', $supply, data: [
            'quantity' => 3,
            'reason' => 'damage',
        ]);

    expect($supply->refresh()->quantity_available)->toBe(7);
});

it('hides manualAdjustment when there is no stock available', function () {
    loginAsAdmin();
    $supply = Supply::factory()->create(['quantity_available' => 0]);

    Livewire::test(ListSupplies::class)
        ->assertTableActionHidden('manualAdjustment', $supply);
});

it('hides the manualAdjustment action from viewers', function () {
    loginAsViewer();
    $supply = Supply::factory()->create(['quantity_available' => 10]);

    Livewire::test(ListSupplies::class)
        ->assertTableActionHidden('manualAdjustment', $supply);
});

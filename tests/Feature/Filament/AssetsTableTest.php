<?php

use App\Filament\Resources\Assets\Pages\ListAssets;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    createRolesAndPermissions();
    $this->actingAs(User::factory()->admin()->create());
});

it('filters by status', function () {
    $available = Asset::factory()->available()->create();
    $maintenance = Asset::factory()->maintenance()->create();

    Livewire::test(ListAssets::class)
        ->filterTable('status', 'maintenance')
        ->assertCanSeeTableRecords([$maintenance])
        ->assertCanNotSeeTableRecords([$available]);
});

it('filters by category', function () {
    $hardware = AssetCategory::factory()->create();
    $peripherals = AssetCategory::factory()->create();

    $hardwareAsset = Asset::factory()->create(['asset_category_id' => $hardware->id]);
    $peripheralAsset = Asset::factory()->create(['asset_category_id' => $peripherals->id]);

    Livewire::test(ListAssets::class)
        ->filterTable('asset_category_id', $hardware->id)
        ->assertCanSeeTableRecords([$hardwareAsset])
        ->assertCanNotSeeTableRecords([$peripheralAsset]);
});

it('filters by location', function () {
    $central = Location::factory()->create();
    $branch = Location::factory()->create();

    $centralAsset = Asset::factory()->create(['location_id' => $central->id]);
    $branchAsset = Asset::factory()->create(['location_id' => $branch->id]);

    Livewire::test(ListAssets::class)
        ->filterTable('location_id', $central->id)
        ->assertCanSeeTableRecords([$centralAsset])
        ->assertCanNotSeeTableRecords([$branchAsset]);
});

it('filters by supplier', function () {
    $dell = Supplier::factory()->create();
    $hp = Supplier::factory()->create();

    $dellAsset = Asset::factory()->create(['supplier_id' => $dell->id]);
    $hpAsset = Asset::factory()->create(['supplier_id' => $hp->id]);

    Livewire::test(ListAssets::class)
        ->filterTable('supplier_id', $dell->id)
        ->assertCanSeeTableRecords([$dellAsset])
        ->assertCanNotSeeTableRecords([$hpAsset]);
});

it('searches by asset tag, name and serial number', function () {
    $target = Asset::factory()->create(['asset_tag' => 'IT-9999', 'name' => 'Findable Laptop', 'serial_number' => 'SN-UNIQUE-1']);
    $other = Asset::factory()->create(['asset_tag' => 'IT-0001', 'name' => 'Other Asset', 'serial_number' => 'SN-OTHER']);

    Livewire::test(ListAssets::class)
        ->searchTable('IT-9999')
        ->assertCanSeeTableRecords([$target])
        ->assertCanNotSeeTableRecords([$other]);

    Livewire::test(ListAssets::class)
        ->searchTable('Findable')
        ->assertCanSeeTableRecords([$target])
        ->assertCanNotSeeTableRecords([$other]);

    Livewire::test(ListAssets::class)
        ->searchTable('SN-UNIQUE-1')
        ->assertCanSeeTableRecords([$target])
        ->assertCanNotSeeTableRecords([$other]);
});

it('sorts by asset tag by default', function () {
    $third = Asset::factory()->create(['asset_tag' => 'IT-0003']);
    $first = Asset::factory()->create(['asset_tag' => 'IT-0001']);
    $second = Asset::factory()->create(['asset_tag' => 'IT-0002']);

    Livewire::test(ListAssets::class)
        ->assertCanSeeTableRecords([$first, $second, $third], inOrder: true);
});

it('hides serial number, warranty and created_at columns by default, keeping core columns visible', function () {
    $test = Livewire::test(ListAssets::class);
    $table = $test->instance()->getTable();

    expect($table->getColumn('serial_number')->isToggledHiddenByDefault())->toBeTrue()
        ->and($table->getColumn('warranty_expiry_date')->isToggledHiddenByDefault())->toBeTrue()
        ->and($table->getColumn('created_at')->isToggledHiddenByDefault())->toBeTrue()
        ->and($table->getColumn('asset_tag')->isToggledHiddenByDefault())->toBeFalse()
        ->and($table->getColumn('name')->isToggledHiddenByDefault())->toBeFalse()
        ->and($table->getColumn('status')->isToggledHiddenByDefault())->toBeFalse();
});

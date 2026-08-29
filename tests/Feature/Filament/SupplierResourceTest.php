<?php

use App\Filament\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Filament\Resources\Suppliers\Pages\ViewSupplier;
use App\Filament\Resources\Suppliers\RelationManagers\AssetsRelationManager;
use App\Filament\Resources\Suppliers\RelationManagers\LicensesRelationManager;
use App\Filament\Resources\Suppliers\RelationManagers\MaintenanceRecordsRelationManager;
use App\Models\Asset;
use App\Models\License;
use App\Models\MaintenanceRecord;
use App\Models\Supplier;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('lists suppliers', function () {
    Supplier::factory()->count(3)->create();

    $this->get('/admin/suppliers')->assertOk();
});

it('creates a supplier', function () {
    Livewire::test(CreateSupplier::class)
        ->fillForm([
            'name' => 'Acme Corp',
            'email' => 'ventas@acme.com',
            'phone' => '555-1234',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Supplier::where('name', 'Acme Corp')->exists())->toBeTrue();
});

it('requires a name to create', function () {
    Livewire::test(CreateSupplier::class)
        ->fillForm(['name' => ''])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('rejects a duplicate name', function () {
    Supplier::factory()->create(['name' => 'Acme Corp']);

    Livewire::test(CreateSupplier::class)
        ->fillForm(['name' => 'Acme Corp'])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique']);
});

it('rejects a duplicate email', function () {
    Supplier::factory()->create(['email' => 'ventas@acme.com']);

    Livewire::test(CreateSupplier::class)
        ->fillForm(['name' => 'Otro Proveedor', 'email' => 'ventas@acme.com'])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);
});

it('allows keeping its own name and email when editing', function () {
    $supplier = Supplier::factory()->create(['name' => 'Acme Corp', 'email' => 'ventas@acme.com']);

    Livewire::test(EditSupplier::class, ['record' => $supplier->getRouteKey()])
        ->fillForm(['name' => 'Acme Corp', 'email' => 'ventas@acme.com'])
        ->call('save')
        ->assertHasNoFormErrors();
});

it('edits a supplier', function () {
    $supplier = Supplier::factory()->create();

    Livewire::test(EditSupplier::class, ['record' => $supplier->getRouteKey()])
        ->fillForm(['name' => 'Updated name'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($supplier->fresh()->name)->toBe('Updated name');
});

it('returns 404 for a non-existent supplier', function () {
    $this->get('/admin/suppliers/99999')->assertNotFound();
});

it('denies viewer from creating a supplier', function () {
    loginAsViewer();

    Livewire::test(CreateSupplier::class)->assertForbidden();
});

it('denies viewer from editing a supplier', function () {
    $supplier = Supplier::factory()->create();
    loginAsViewer();

    Livewire::test(EditSupplier::class, ['record' => $supplier->getRouteKey()])->assertForbidden();
});

it('hides the delete action from editor on the edit page', function () {
    $supplier = Supplier::factory()->create();
    loginAsEditor();

    Livewire::test(EditSupplier::class, ['record' => $supplier->getRouteKey()])
        ->assertActionHidden('delete');
});

it('allows deleting a supplier with no associations', function () {
    $supplier = Supplier::factory()->create();

    Livewire::test(EditSupplier::class, ['record' => $supplier->getRouteKey()])
        ->callAction('delete');

    expect(Supplier::find($supplier->id))->toBeNull();
});

it('allows deleting a supplier with associated assets, licenses and maintenance records, nulling the FK', function () {
    $supplier = Supplier::factory()->create();
    $asset = Asset::factory()->create(['supplier_id' => $supplier->id]);
    $license = License::factory()->create(['supplier_id' => $supplier->id]);
    $maintenanceRecord = MaintenanceRecord::factory()->create(['supplier_id' => $supplier->id]);

    Livewire::test(EditSupplier::class, ['record' => $supplier->getRouteKey()])
        ->callAction('delete');

    expect(Supplier::find($supplier->id))->toBeNull()
        ->and($asset->fresh()->supplier_id)->toBeNull()
        ->and($license->fresh()->supplier_id)->toBeNull()
        ->and($maintenanceRecord->fresh()->supplier_id)->toBeNull();
});

it('shows the assets belonging to the supplier in the Activos tab', function () {
    $supplier = Supplier::factory()->create();
    $asset = Asset::factory()->create(['supplier_id' => $supplier->id]);
    Asset::factory()->create();

    Livewire::test(AssetsRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => ViewSupplier::class,
    ])->assertCanSeeTableRecords([$asset]);
});

it('shows the licenses belonging to the supplier in the Licencias tab', function () {
    $supplier = Supplier::factory()->create();
    $license = License::factory()->create(['supplier_id' => $supplier->id]);
    License::factory()->create();

    Livewire::test(LicensesRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => ViewSupplier::class,
    ])->assertCanSeeTableRecords([$license]);
});

it('shows the maintenance records belonging to the supplier in the Mantenimientos tab', function () {
    $supplier = Supplier::factory()->create();
    $maintenanceRecord = MaintenanceRecord::factory()->create(['supplier_id' => $supplier->id]);
    MaintenanceRecord::factory()->create();

    Livewire::test(MaintenanceRecordsRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => ViewSupplier::class,
    ])->assertCanSeeTableRecords([$maintenanceRecord]);
});

it('has no create/edit/delete actions on the Activos tab (read-only)', function () {
    $supplier = Supplier::factory()->create();

    Livewire::test(AssetsRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => ViewSupplier::class,
    ])
        ->assertTableActionDoesNotExist('create')
        ->assertTableActionDoesNotExist('edit')
        ->assertTableActionDoesNotExist('delete');
});

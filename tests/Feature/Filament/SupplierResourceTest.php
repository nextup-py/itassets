<?php

use App\Filament\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
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

it('allows duplicate emails (no uniqueness rule exists today)', function () {
    Supplier::factory()->create(['email' => 'ventas@acme.com']);

    Livewire::test(CreateSupplier::class)
        ->fillForm(['name' => 'Otro Proveedor', 'email' => 'ventas@acme.com'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Supplier::where('email', 'ventas@acme.com')->count())->toBe(2);
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

it('hides the delete bulk action from editor on the list page', function () {
    Supplier::factory()->create();
    loginAsEditor();

    Livewire::test(ListSuppliers::class)->assertTableBulkActionHidden('delete');
});

<?php

use App\Filament\Resources\ServiceContracts\Pages\CreateServiceContract;
use App\Filament\Resources\ServiceContracts\Pages\EditServiceContract;
use App\Filament\Resources\ServiceContracts\Pages\ListServiceContracts;
use App\Models\Asset;
use App\Models\ServiceContract;
use App\Models\Supplier;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('lists service contracts', function () {
    ServiceContract::factory()->count(3)->create();

    $this->get('/service-contracts')->assertOk();
});

it('creates a service contract', function () {
    $supplier = Supplier::factory()->create();

    Livewire::test(CreateServiceContract::class)
        ->fillForm([
            'name' => 'Soporte anual de red',
            'contract_type' => 'support',
            'supplier_id' => $supplier->id,
            'expiry_date' => now()->addYear()->toDateString(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(ServiceContract::where('name', 'Soporte anual de red')->exists())->toBeTrue();
});

it('requires name, contract_type, supplier_id and expiry_date to create', function () {
    Livewire::test(CreateServiceContract::class)
        ->fillForm(['name' => ''])
        ->call('create')
        ->assertHasFormErrors(['name', 'contract_type', 'supplier_id', 'expiry_date']);
});

it('creates a contract covering specific assets', function () {
    $supplier = Supplier::factory()->create();
    $assets = Asset::factory()->count(2)->create();

    Livewire::test(CreateServiceContract::class)
        ->fillForm([
            'name' => 'Garantía extendida servidores',
            'contract_type' => 'extended_warranty',
            'supplier_id' => $supplier->id,
            'expiry_date' => now()->addYear()->toDateString(),
            'assets' => $assets->pluck('id')->toArray(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $contract = ServiceContract::where('name', 'Garantía extendida servidores')->first();
    expect($contract->assets)->toHaveCount(2);
});

it('creates a general contract with no assets attached', function () {
    $supplier = Supplier::factory()->create();

    Livewire::test(CreateServiceContract::class)
        ->fillForm([
            'name' => 'Soporte general de red',
            'contract_type' => 'support',
            'supplier_id' => $supplier->id,
            'expiry_date' => now()->addYear()->toDateString(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $contract = ServiceContract::where('name', 'Soporte general de red')->first();
    expect($contract->assets)->toHaveCount(0);
});

it('edits a service contract', function () {
    $contract = ServiceContract::factory()->create(['name' => 'Old name', 'expiry_date' => now()->addYear()]);

    Livewire::test(EditServiceContract::class, ['record' => $contract->getRouteKey()])
        ->fillForm(['name' => 'New name'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($contract->fresh()->name)->toBe('New name');
});

it('renews a contract, updating its expiry date', function () {
    $contract = ServiceContract::factory()->create(['expiry_date' => now()->addDays(10)]);
    $newDate = now()->addYear()->toDateString();

    Livewire::test(ListServiceContracts::class)
        ->callTableAction('renew', $contract, data: ['expiry_date' => $newDate])
        ->assertHasNoTableActionErrors();

    expect($contract->fresh()->expiry_date->toDateString())->toBe($newDate);
});

it('filters contracts by expired status', function () {
    $expired = ServiceContract::factory()->create(['expiry_date' => now()->subDays(5)]);
    $valid = ServiceContract::factory()->create(['expiry_date' => now()->addDays(120)]);

    Livewire::test(ListServiceContracts::class)
        ->filterTable('expiry_status', ['value' => 'expired'])
        ->assertCanSeeTableRecords([$expired])
        ->assertCanNotSeeTableRecords([$valid]);
});

it('denies viewer from creating a service contract', function () {
    loginAsViewer();

    Livewire::test(CreateServiceContract::class)->assertForbidden();
});

it('hides the delete action from editor on the edit page', function () {
    $contract = ServiceContract::factory()->create();
    loginAsEditor();

    Livewire::test(EditServiceContract::class, ['record' => $contract->getRouteKey()])
        ->assertActionHidden('delete');
});

it('returns 404 for a non-existent service contract', function () {
    $this->get('/service-contracts/99999')->assertNotFound();
});

<?php

use App\Filament\Resources\Supplies\Pages\CreateSupply;
use App\Filament\Resources\Supplies\Pages\EditSupply;
use App\Filament\Resources\Supplies\Pages\ListSupplies;
use App\Models\Supply;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('lists supplies', function () {
    Supply::factory()->count(3)->create();

    $this->get('/admin/supplies')->assertOk();
});

it('creates a supply', function () {
    Livewire::test(CreateSupply::class)
        ->fillForm([
            'name' => 'Disco duro 1TB',
            'quantity_available' => 10,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Supply::where('name', 'Disco duro 1TB')->exists())->toBeTrue();
});

it('edits a supply', function () {
    $supply = Supply::factory()->create();

    Livewire::test(EditSupply::class, ['record' => $supply->getRouteKey()])
        ->fillForm(['name' => 'Updated name'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($supply->fresh()->name)->toBe('Updated name');
});

it('denies viewer from creating a supply', function () {
    loginAsViewer();

    Livewire::test(CreateSupply::class)->assertForbidden();
});

it('hides the delete bulk action from editor on the list page', function () {
    Supply::factory()->create();
    loginAsEditor();

    Livewire::test(ListSupplies::class)->assertTableBulkActionHidden('delete');
});

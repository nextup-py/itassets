<?php

use App\Filament\Resources\Locations\Pages\CreateLocation;
use App\Filament\Resources\Locations\Pages\EditLocation;
use App\Filament\Resources\Locations\Pages\ListLocations;
use App\Models\Location;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('lists locations', function () {
    Location::factory()->count(3)->create();

    $this->get('/admin/locations')->assertOk();
});

it('creates a location', function () {
    Livewire::test(CreateLocation::class)
        ->fillForm([
            'name' => 'Sede Central',
            'building' => 'Torre A',
            'floor' => 'Piso 3',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Location::where('name', 'Sede Central')->exists())->toBeTrue();
});

it('requires a name to create', function () {
    Livewire::test(CreateLocation::class)
        ->fillForm(['name' => ''])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('edits a location', function () {
    $location = Location::factory()->create();

    Livewire::test(EditLocation::class, ['record' => $location->getRouteKey()])
        ->fillForm(['name' => 'Updated name'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($location->fresh()->name)->toBe('Updated name');
});

it('returns 404 for a non-existent location', function () {
    $this->get('/admin/locations/99999')->assertNotFound();
});

it('denies viewer from creating a location', function () {
    loginAsViewer();

    Livewire::test(CreateLocation::class)->assertForbidden();
});

it('denies viewer from editing a location', function () {
    $location = Location::factory()->create();
    loginAsViewer();

    Livewire::test(EditLocation::class, ['record' => $location->getRouteKey()])->assertForbidden();
});

it('hides the delete action from editor on the edit page', function () {
    $location = Location::factory()->create();
    loginAsEditor();

    Livewire::test(EditLocation::class, ['record' => $location->getRouteKey()])
        ->assertActionHidden('delete');
});

it('hides the delete bulk action from editor on the list page', function () {
    Location::factory()->create();
    loginAsEditor();

    Livewire::test(ListLocations::class)->assertTableBulkActionHidden('delete');
});

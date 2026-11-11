<?php

use App\Filament\Resources\Locations\Pages\CreateLocation;
use App\Filament\Resources\Locations\Pages\EditLocation;
use App\Filament\Resources\Locations\Pages\ListLocations;
use App\Filament\Resources\Locations\Pages\ViewLocation;
use App\Filament\Resources\Locations\RelationManagers\AssetsRelationManager;
use App\Filament\Resources\Locations\RelationManagers\SuppliesRelationManager;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Supply;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('lists locations', function () {
    Location::factory()->count(3)->create();

    $this->get('/locations')->assertOk();
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

it('rejects a duplicate name', function () {
    Location::factory()->create(['name' => 'Sede Central']);

    Livewire::test(CreateLocation::class)
        ->fillForm(['name' => 'Sede Central'])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique']);
});

it('allows keeping its own name when editing', function () {
    $location = Location::factory()->create(['name' => 'Sede Central']);

    Livewire::test(EditLocation::class, ['record' => $location->getRouteKey()])
        ->fillForm(['name' => 'Sede Central'])
        ->call('save')
        ->assertHasNoFormErrors();
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
    $this->get('/locations/99999')->assertNotFound();
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

it('allows deleting a location with no assets attached', function () {
    $location = Location::factory()->create();

    Livewire::test(EditLocation::class, ['record' => $location->getRouteKey()])
        ->callAction('delete');

    expect(Location::find($location->id))->toBeNull();
});

it('allows deleting a location with associated assets, nulling the FK', function () {
    $location = Location::factory()->create();
    $asset = Asset::factory()->create(['location_id' => $location->id]);

    Livewire::test(EditLocation::class, ['record' => $location->getRouteKey()])
        ->callAction('delete');

    expect(Location::find($location->id))->toBeNull()
        ->and($asset->fresh()->location_id)->toBeNull();
});

it('shows the assets belonging to the location in the Activos tab', function () {
    $location = Location::factory()->create();
    $asset = Asset::factory()->create(['location_id' => $location->id]);
    Asset::factory()->create();

    Livewire::test(AssetsRelationManager::class, [
        'ownerRecord' => $location,
        'pageClass' => ViewLocation::class,
    ])->assertCanSeeTableRecords([$asset]);
});

it('has no create/edit/delete actions on the Activos tab (read-only)', function () {
    $location = Location::factory()->create();

    Livewire::test(AssetsRelationManager::class, [
        'ownerRecord' => $location,
        'pageClass' => ViewLocation::class,
    ])
        ->assertTableActionDoesNotExist('create')
        ->assertTableActionDoesNotExist('edit')
        ->assertTableActionDoesNotExist('delete');
});

it('shows the supplies belonging to the location in the Insumos tab', function () {
    $location = Location::factory()->create();
    $supply = Supply::factory()->create(['location_id' => $location->id]);
    Supply::factory()->create();

    Livewire::test(SuppliesRelationManager::class, [
        'ownerRecord' => $location,
        'pageClass' => ViewLocation::class,
    ])->assertCanSeeTableRecords([$supply]);
});

it('shows the asset and supply counts on the location view page', function () {
    $location = Location::factory()->create();
    Asset::factory()->count(2)->create(['location_id' => $location->id]);
    Supply::factory()->count(3)->create(['location_id' => $location->id]);

    Livewire::test(ViewLocation::class, ['record' => $location->getRouteKey()])
        ->assertSee('2')
        ->assertSee('3');
});

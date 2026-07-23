<?php

use App\Filament\Resources\AssetCategories\Pages\CreateAssetCategory;
use App\Filament\Resources\AssetCategories\Pages\EditAssetCategory;
use App\Filament\Resources\AssetCategories\Pages\ListAssetCategories;
use App\Filament\Resources\AssetCategories\Pages\ViewAssetCategory;
use App\Filament\Resources\AssetCategories\RelationManagers\AssetsRelationManager;
use App\Models\Asset;
use App\Models\AssetCategory;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('lists asset categories', function () {
    AssetCategory::factory()->count(3)->create();

    $this->get('/admin/asset-categories')->assertOk();
});

it('creates an asset category', function () {
    Livewire::test(CreateAssetCategory::class)
        ->fillForm([
            'name' => 'Laptops',
            'description' => 'Equipos portátiles',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(AssetCategory::where('name', 'Laptops')->exists())->toBeTrue();
});

it('requires a name to create', function () {
    Livewire::test(CreateAssetCategory::class)
        ->fillForm(['name' => ''])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('rejects a duplicate name', function () {
    AssetCategory::factory()->create(['name' => 'Laptops']);

    Livewire::test(CreateAssetCategory::class)
        ->fillForm(['name' => 'Laptops'])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique']);
});

it('allows keeping its own name when editing', function () {
    $category = AssetCategory::factory()->create(['name' => 'Laptops']);

    Livewire::test(EditAssetCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['name' => 'Laptops'])
        ->call('save')
        ->assertHasNoFormErrors();
});

it('edits an asset category', function () {
    $category = AssetCategory::factory()->create();

    Livewire::test(EditAssetCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['name' => 'Updated name'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($category->fresh()->name)->toBe('Updated name');
});

it('returns 404 for a non-existent asset category', function () {
    $this->get('/admin/asset-categories/99999')->assertNotFound();
});

it('denies viewer from creating an asset category', function () {
    loginAsViewer();

    Livewire::test(CreateAssetCategory::class)->assertForbidden();
});

it('denies viewer from editing an asset category', function () {
    $category = AssetCategory::factory()->create();
    loginAsViewer();

    Livewire::test(EditAssetCategory::class, ['record' => $category->getRouteKey()])->assertForbidden();
});

it('hides the delete action from editor on the edit page', function () {
    $category = AssetCategory::factory()->create();
    loginAsEditor();

    Livewire::test(EditAssetCategory::class, ['record' => $category->getRouteKey()])
        ->assertActionHidden('delete');
});

it('hides the delete bulk action from editor on the list page', function () {
    AssetCategory::factory()->create();
    loginAsEditor();

    Livewire::test(ListAssetCategories::class)->assertTableBulkActionHidden('delete');
});

it('shows the assets belonging to the category in the Activos tab', function () {
    $category = AssetCategory::factory()->create();
    $asset = Asset::factory()->create(['asset_category_id' => $category->id]);
    Asset::factory()->create();

    Livewire::test(AssetsRelationManager::class, [
        'ownerRecord' => $category,
        'pageClass' => ViewAssetCategory::class,
    ])->assertCanSeeTableRecords([$asset]);
});

it('blocks deleting a category that still has assets attached, with a friendly notification', function () {
    $category = AssetCategory::factory()->create();
    Asset::factory()->create(['asset_category_id' => $category->id]);

    Livewire::test(EditAssetCategory::class, ['record' => $category->getRouteKey()])
        ->callAction('delete')
        ->assertNotified();

    expect(AssetCategory::find($category->id))->not->toBeNull();
});

it('allows deleting a category with no assets attached', function () {
    $category = AssetCategory::factory()->create();

    Livewire::test(EditAssetCategory::class, ['record' => $category->getRouteKey()])
        ->callAction('delete');

    expect(AssetCategory::find($category->id))->toBeNull();
});

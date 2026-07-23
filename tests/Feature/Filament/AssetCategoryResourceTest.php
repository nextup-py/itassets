<?php

use App\Filament\Resources\AssetCategories\Pages\CreateAssetCategory;
use App\Filament\Resources\AssetCategories\Pages\EditAssetCategory;
use App\Filament\Resources\AssetCategories\Pages\ListAssetCategories;
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

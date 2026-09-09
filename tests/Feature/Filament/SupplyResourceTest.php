<?php

use App\Filament\Resources\Supplies\Pages\CreateSupply;
use App\Filament\Resources\Supplies\Pages\EditSupply;
use App\Filament\Resources\Supplies\Pages\ListSupplies;
use App\Models\AssetCategory;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('lists supplies', function () {
    Supply::factory()->count(3)->create();

    $this->get('/supplies')->assertOk();
});

it('creates a supply', function () {
    $category = AssetCategory::factory()->create();

    Livewire::test(CreateSupply::class)
        ->fillForm([
            'name' => 'Disco duro 1TB',
            'asset_category_id' => $category->id,
            'quantity_available' => 10,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Supply::where('name', 'Disco duro 1TB')->exists())->toBeTrue();
});

it('requires a category when creating a supply', function () {
    Livewire::test(CreateSupply::class)
        ->fillForm([
            'name' => 'Disco duro 1TB',
            'quantity_available' => 10,
        ])
        ->call('create')
        ->assertHasFormErrors(['asset_category_id' => 'required']);
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

it('shows the import and template actions to admins (who have import_supply)', function () {
    Livewire::test(ListSupplies::class)
        ->assertActionVisible('importSupplies')
        ->assertActionVisible('downloadTemplate');
});

it('hides the import and template actions from viewers (who lack import_supply)', function () {
    $viewer = User::factory()->viewer()->create();
    $this->actingAs($viewer);

    Livewire::test(ListSupplies::class)
        ->assertActionHidden('importSupplies')
        ->assertActionHidden('downloadTemplate');
});

it('shows the export action to admins (who have export_report)', function () {
    Livewire::test(ListSupplies::class)->assertActionVisible('exportSupplies');
});

it('hides the export action from editors (who lack export_report)', function () {
    $editor = User::factory()->editor()->create();
    $this->actingAs($editor);

    Livewire::test(ListSupplies::class)->assertActionHidden('exportSupplies');
});

it('imports supplies from a real uploaded file through the importSupplies action', function () {
    Storage::fake('local');
    AssetCategory::factory()->create(['name' => 'Periféricos']);

    $csv = "nombre,categoria,cantidad_disponible,proveedor,ubicacion,notas\n"
        . "Uploaded Supply,Periféricos,15,,,\n";
    $file = UploadedFile::fake()->createWithContent('supplies.csv', $csv);

    Livewire::test(ListSupplies::class)
        ->callAction('importSupplies', data: ['file' => $file])
        ->assertHasNoActionErrors();

    expect(Supply::where('name', 'Uploaded Supply')->exists())->toBeTrue();
});


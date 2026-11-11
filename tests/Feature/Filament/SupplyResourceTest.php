<?php

use App\Filament\Resources\Supplies\Pages\CreateSupply;
use App\Filament\Resources\Supplies\Pages\EditSupply;
use App\Filament\Resources\Supplies\Pages\ListSupplies;
use App\Filament\Resources\Supplies\Pages\ViewSupply;
use App\Models\AssetCategory;
use App\Models\Employee;
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

it('ignores a changed quantity_available when editing an existing supply', function () {
    $supply = Supply::factory()->create(['quantity_available' => 5]);

    Livewire::test(EditSupply::class, ['record' => $supply->getRouteKey()])
        ->fillForm(['quantity_available' => 999])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($supply->fresh()->quantity_available)->toBe(5);
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

it('registers a movement to an employee from the view page', function () {
    $supply = Supply::factory()->create(['quantity_available' => 10]);
    $employee = Employee::factory()->create(['is_active' => true]);

    Livewire::test(ViewSupply::class, ['record' => $supply->getRouteKey()])
        ->callAction('registerMovement', data: [
            'type' => 'entrega',
            'quantity' => 2,
            'recipient_type' => 'employee',
            'employee_id' => $employee->id,
            'performed_at' => now()->toDateString(),
        ])
        ->assertHasNoActionErrors();

    expect($supply->fresh()->quantity_available)->toBe(8);
});

it('disposes of damaged stock from the view page', function () {
    $supply = Supply::factory()->create(['quantity_available' => 10, 'quantity_damaged' => 3]);

    Livewire::test(ViewSupply::class, ['record' => $supply->getRouteKey()])
        ->callAction('disposeDamaged', data: [
            'quantity' => 2,
            'reason' => 'disposed',
        ])
        ->assertHasNoActionErrors();

    expect($supply->fresh())
        ->quantity_available->toBe(10)
        ->quantity_damaged->toBe(1);
});

it('hides disposeDamaged when there is no damaged stock', function () {
    $supply = Supply::factory()->create(['quantity_damaged' => 0]);

    Livewire::test(ViewSupply::class, ['record' => $supply->getRouteKey()])
        ->assertActionHidden('disposeDamaged');
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


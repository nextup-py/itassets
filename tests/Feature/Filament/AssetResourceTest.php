<?php

use App\Filament\Resources\Assets\Pages\CreateAsset;
use App\Filament\Resources\Assets\Pages\EditAsset;
use App\Filament\Resources\Assets\Pages\ListAssets;
use App\Filament\Resources\Assets\Pages\ViewAsset;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Assignment;
use App\Models\Employee;
use App\Models\MaintenanceRecord;
use App\Models\User;
use App\Services\AssignmentService;
use Livewire\Livewire;

beforeEach(function () {
    createRolesAndPermissions();
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('lists assets', function () {
    Asset::factory()->count(3)->create();

    $this->get('/admin/assets')->assertOk();
});

it('can render create page', function () {
    $this->get('/admin/assets/create')->assertOk();
});

it('can render edit page', function () {
    $asset = Asset::factory()->create();

    $this->get("/admin/assets/{$asset->id}/edit")->assertOk();
});

it('can render view page', function () {
    $asset = Asset::factory()->create();

    $this->get("/admin/assets/{$asset->id}")->assertOk();
});

it('returns 404 for non-existent asset', function () {
    $this->get('/admin/assets/99999')->assertNotFound();
});

it('has a supplier filter on the assets table', function () {
    Livewire::test(ListAssets::class)->assertTableFilterExists('supplier_id');
});

it('shows the export action to admins (who have export_report)', function () {
    Livewire::test(ListAssets::class)->assertActionVisible('exportAssets');
});

it('hides the export action from editors (who lack export_report)', function () {
    $editor = User::factory()->editor()->create();
    $this->actingAs($editor);

    Livewire::test(ListAssets::class)->assertActionHidden('exportAssets');
});

it('shows the asset lifecycle actions to admins (who have update_asset)', function () {
    $available = Asset::factory()->available()->create();
    Livewire::test(ViewAsset::class, ['record' => $available->getRouteKey()])
        ->assertActionVisible('assign')
        ->assertActionVisible('sendToMaintenance');

    $assigned = Asset::factory()->assigned()->create();
    Livewire::test(ViewAsset::class, ['record' => $assigned->getRouteKey()])
        ->assertActionVisible('return');

    $inMaintenance = Asset::factory()->maintenance()->create();
    Livewire::test(ViewAsset::class, ['record' => $inMaintenance->getRouteKey()])
        ->assertActionVisible('closeMaintenance');
});

it('shows the asset lifecycle actions to editors (who have update_asset)', function () {
    $editor = User::factory()->editor()->create();
    $this->actingAs($editor);

    $available = Asset::factory()->available()->create();
    Livewire::test(ViewAsset::class, ['record' => $available->getRouteKey()])
        ->assertActionVisible('assign')
        ->assertActionVisible('sendToMaintenance');

    $assigned = Asset::factory()->assigned()->create();
    Livewire::test(ViewAsset::class, ['record' => $assigned->getRouteKey()])
        ->assertActionVisible('return');

    $inMaintenance = Asset::factory()->maintenance()->create();
    Livewire::test(ViewAsset::class, ['record' => $inMaintenance->getRouteKey()])
        ->assertActionVisible('closeMaintenance');
});

it('hides the asset lifecycle actions from viewers (who lack update_asset)', function () {
    $viewer = User::factory()->viewer()->create();
    $this->actingAs($viewer);

    $available = Asset::factory()->available()->create();
    Livewire::test(ViewAsset::class, ['record' => $available->getRouteKey()])
        ->assertActionHidden('assign')
        ->assertActionHidden('sendToMaintenance');

    $assigned = Asset::factory()->assigned()->create();
    Livewire::test(ViewAsset::class, ['record' => $assigned->getRouteKey()])
        ->assertActionHidden('return');

    $inMaintenance = Asset::factory()->maintenance()->create();
    Livewire::test(ViewAsset::class, ['record' => $inMaintenance->getRouteKey()])
        ->assertActionHidden('closeMaintenance');
});

it('creates an asset with the minimum required fields', function () {
    $category = AssetCategory::factory()->create();

    Livewire::test(CreateAsset::class)
        ->fillForm([
            'name' => 'Laptop Dell Latitude',
            'asset_category_id' => $category->id,
            'status' => 'stock',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Asset::where('name', 'Laptop Dell Latitude')->exists())->toBeTrue();
});

it('requires name and asset_category_id to create', function () {
    Livewire::test(CreateAsset::class)
        ->fillForm(['name' => ''])
        ->call('create')
        ->assertHasFormErrors(['name', 'asset_category_id']);
});

it('auto-generates the asset_tag through the create form when left blank', function () {
    $category = AssetCategory::factory()->create();

    Livewire::test(CreateAsset::class)
        ->fillForm([
            'name' => 'Monitor LG 27"',
            'asset_category_id' => $category->id,
            'status' => 'stock',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $asset = Asset::where('name', 'Monitor LG 27"')->first();
    expect($asset->asset_tag)->toMatch('/^IT-\d{4}$/');
});

it('edits an asset', function () {
    $asset = Asset::factory()->create(['name' => 'Old name', 'condition' => 'good']);

    Livewire::test(EditAsset::class, ['record' => $asset->getRouteKey()])
        ->fillForm(['name' => 'New name', 'condition' => 'poor'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($asset->fresh()->name)->toBe('New name');
    expect($asset->fresh()->condition)->toBe('poor');
});

it('assigns an asset to an employee via the assign action', function () {
    $asset = Asset::factory()->available()->create();
    $employee = Employee::factory()->create();

    Livewire::test(ViewAsset::class, ['record' => $asset->getRouteKey()])
        ->callAction('assign', data: [
            'employee_id' => $employee->id,
            'assigned_at' => now()->toDateString(),
        ])
        ->assertHasNoActionErrors();

    expect($asset->fresh()->status)->toBe('assigned');
    expect(Assignment::where('employee_id', $employee->id)->exists())->toBeTrue();
});

it('returns an assigned asset via the return action', function () {
    $asset = Asset::factory()->available()->create();
    $employee = Employee::factory()->create();

    app(AssignmentService::class)->assign($asset, [
        'employee_id' => $employee->id,
        'assigned_at' => now()->subDays(5)->toDateString(),
    ]);

    expect($asset->fresh()->status)->toBe('assigned');

    Livewire::test(ViewAsset::class, ['record' => $asset->getRouteKey()])
        ->callAction('return', data: [
            'returned_at' => now()->toDateString(),
        ])
        ->assertHasNoActionErrors();

    expect($asset->fresh()->status)->toBe('available');
});

it('sends an asset to maintenance via the sendToMaintenance action', function () {
    $asset = Asset::factory()->available()->create();

    Livewire::test(ViewAsset::class, ['record' => $asset->getRouteKey()])
        ->callAction('sendToMaintenance', data: [
            'type' => 'repair',
            'description' => 'Pantalla rota',
            'started_at' => now()->toDateString(),
        ])
        ->assertHasNoActionErrors();

    expect($asset->fresh()->status)->toBe('maintenance');
    expect(MaintenanceRecord::where('asset_id', $asset->id)->exists())->toBeTrue();
});

it('closes maintenance via the closeMaintenance action, setting the chosen asset status', function () {
    $asset = Asset::factory()->maintenance()->create();
    MaintenanceRecord::factory()->inProgress()->for($asset)->create();

    Livewire::test(ViewAsset::class, ['record' => $asset->getRouteKey()])
        ->callAction('closeMaintenance', data: [
            'new_asset_status' => 'retired',
            'completed_at' => now()->toDateString(),
        ])
        ->assertHasNoActionErrors();

    expect($asset->fresh()->status)->toBe('retired');
});

it('shows the import and template actions to admins (who have import_asset)', function () {
    Livewire::test(ListAssets::class)
        ->assertActionVisible('importAssets')
        ->assertActionVisible('downloadTemplate');
});

it('hides the import and template actions from viewers (who lack import_asset)', function () {
    $viewer = User::factory()->viewer()->create();
    $this->actingAs($viewer);

    Livewire::test(ListAssets::class)
        ->assertActionHidden('importAssets')
        ->assertActionHidden('downloadTemplate');
});

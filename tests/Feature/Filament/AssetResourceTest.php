<?php

use App\Filament\Resources\Assets\Pages\ListAssets;
use App\Filament\Resources\Assets\Pages\ViewAsset;
use App\Models\Asset;
use App\Models\User;
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

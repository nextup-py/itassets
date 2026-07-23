<?php

use App\Filament\Pages\Reports;
use App\Models\User;
use Livewire\Livewire;

it('shows the export actions to admins (who have export_report)', function () {
    createRolesAndPermissions();
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Reports::class)
        ->assertActionVisible('exportAssets')
        ->assertActionVisible('exportAssignments');
});

it('hides the export actions from editors (who lack export_report)', function () {
    createRolesAndPermissions();
    $this->actingAs(User::factory()->editor()->create());

    Livewire::test(Reports::class)
        ->assertActionHidden('exportAssets')
        ->assertActionHidden('exportAssignments');
});

it('hides the export actions from viewers', function () {
    createRolesAndPermissions();
    $this->actingAs(User::factory()->viewer()->create());

    Livewire::test(Reports::class)
        ->assertActionHidden('exportAssets')
        ->assertActionHidden('exportAssignments');
});

it('still lets everyone see the page and its stats', function () {
    createRolesAndPermissions();
    $this->actingAs(User::factory()->viewer()->create());

    $this->get('/admin/reports')->assertOk();
});

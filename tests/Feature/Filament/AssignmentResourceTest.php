<?php

use App\Filament\Resources\Assignments\Pages\CreateAssignment;
use App\Filament\Resources\Assignments\Pages\EditAssignment;
use App\Filament\Resources\Assignments\Pages\ListAssignments;
use App\Models\Asset;
use App\Models\Assignment;
use App\Models\Employee;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('edits an assignment without changing its already-assigned assets', function () {
    $asset = Asset::factory()->available()->create();
    $assignment = Assignment::factory()->create();
    $assignment->assets()->attach($asset->id, ['assigned_at' => $assignment->assigned_at]);

    expect($asset->fresh()->status)->toBe('assigned');

    Livewire::test(EditAssignment::class, ['record' => $assignment->getRouteKey()])
        ->fillForm(['notes' => 'Updated note'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($assignment->fresh()->notes)->toBe('Updated note');
});

it('lets removing an asset from an assignment while editing', function () {
    $assetToKeep = Asset::factory()->available()->create();
    $assetToRemove = Asset::factory()->available()->create();
    $assignment = Assignment::factory()->create();
    $assignment->assets()->attach($assetToKeep->id, ['assigned_at' => $assignment->assigned_at]);
    $assignment->assets()->attach($assetToRemove->id, ['assigned_at' => $assignment->assigned_at]);

    Livewire::test(EditAssignment::class, ['record' => $assignment->getRouteKey()])
        ->fillForm([
            'assets' => [
                ['asset_id' => $assetToKeep->id, 'charger_serial' => null, 'ticket_number' => null],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($assignment->fresh()->assets->pluck('id')->all())->toBe([$assetToKeep->id]);
    expect($assetToRemove->fresh()->status)->toBe('available');
});

it('does not offer assets already assigned to another assignment when creating', function () {
    $assignedElsewhere = Asset::factory()->assigned()->create();
    $available = Asset::factory()->available()->create();

    Livewire::test(CreateAssignment::class)
        ->assertFormFieldExists('assets')
        ->fillForm([
            'employee_id' => \App\Models\Employee::factory()->create()->id,
            'assigned_at' => now()->toDateString(),
            'assets' => [
                ['asset_id' => $assignedElsewhere->id],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['assets.0.asset_id']);

    $this->assertTrue($available->exists);
});

it('fills in assigned_by from the authenticated user when created from this resource', function () {
    $admin = auth()->user();
    $employee = Employee::factory()->create();
    $asset = Asset::factory()->available()->create();

    Livewire::test(CreateAssignment::class)
        ->fillForm([
            'employee_id' => $employee->id,
            'assigned_at' => now()->toDateString(),
            'assets' => [
                ['asset_id' => $asset->id],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $assignment = Assignment::where('employee_id', $employee->id)->first();
    expect($assignment->assigned_by)->toBe($admin->name);
});

it('has an employee filter on the assignments table', function () {
    Livewire::test(ListAssignments::class)->assertTableFilterExists('employee_id');
});

it('shows the employee department name, not a raw object, in the department column', function () {
    $department = \App\Models\Department::factory()->create(['name' => 'Soporte Técnico']);
    $employee = Employee::factory()->create(['department_id' => $department->id]);
    Assignment::factory()->create(['employee_id' => $employee->id]);

    Livewire::test(ListAssignments::class)
        ->toggleAllTableColumns()
        ->assertSee('Soporte Técnico');
});

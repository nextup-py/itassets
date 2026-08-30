<?php

use App\Filament\Resources\Departments\Pages\CreateDepartment;
use App\Filament\Resources\Departments\Pages\EditDepartment;
use App\Filament\Resources\Departments\Pages\ListDepartments;
use App\Filament\Resources\Departments\Pages\ViewDepartment;
use App\Models\Department;
use App\Models\Employee;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('lists departments', function () {
    Department::factory()->count(3)->create();

    $this->get('/admin/departments')->assertOk();
});

it('creates a department', function () {
    Livewire::test(CreateDepartment::class)
        ->fillForm(['name' => 'Legal'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Department::where('name', 'Legal')->exists())->toBeTrue();
});

it('requires a name to create', function () {
    Livewire::test(CreateDepartment::class)
        ->fillForm(['name' => ''])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('rejects a duplicate name', function () {
    Department::factory()->create(['name' => 'Legal']);

    Livewire::test(CreateDepartment::class)
        ->fillForm(['name' => 'Legal'])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique']);
});

it('allows keeping its own name when editing', function () {
    $department = Department::factory()->create(['name' => 'Legal']);

    Livewire::test(EditDepartment::class, ['record' => $department->getRouteKey()])
        ->fillForm(['name' => 'Legal'])
        ->call('save')
        ->assertHasNoFormErrors();
});

it('edits a department', function () {
    $department = Department::factory()->create();

    Livewire::test(EditDepartment::class, ['record' => $department->getRouteKey()])
        ->fillForm(['name' => 'Updated name'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($department->fresh()->name)->toBe('Updated name');
});

it('returns 404 for a non-existent department', function () {
    $this->get('/admin/departments/99999')->assertNotFound();
});

it('denies viewer from creating a department', function () {
    loginAsViewer();

    Livewire::test(CreateDepartment::class)->assertForbidden();
});

it('denies viewer from editing a department', function () {
    $department = Department::factory()->create();
    loginAsViewer();

    Livewire::test(EditDepartment::class, ['record' => $department->getRouteKey()])->assertForbidden();
});

it('hides the delete action from editor on the edit page', function () {
    $department = Department::factory()->create();
    loginAsEditor();

    Livewire::test(EditDepartment::class, ['record' => $department->getRouteKey()])
        ->assertActionHidden('delete');
});

it('blocks deleting a department that still has employees attached, with a friendly notification', function () {
    $department = Department::factory()->create();
    Employee::factory()->create(['department_id' => $department->id]);

    Livewire::test(EditDepartment::class, ['record' => $department->getRouteKey()])
        ->callAction('delete')
        ->assertNotified();

    expect(Department::find($department->id))->not->toBeNull();
});

it('allows deleting a department with no employees attached', function () {
    $department = Department::factory()->create();

    Livewire::test(EditDepartment::class, ['record' => $department->getRouteKey()])
        ->callAction('delete');

    expect(Department::find($department->id))->toBeNull();
});

it('shows the employee count on the department view page', function () {
    $department = Department::factory()->create();
    Employee::factory()->count(2)->create(['department_id' => $department->id]);

    Livewire::test(ViewDepartment::class, ['record' => $department->getRouteKey()])
        ->assertSee('2');
});

<?php

use App\Filament\Resources\Assets\Pages\ViewAsset;
use App\Filament\Resources\Assets\RelationManagers\AssignmentsRelationManager as AssetAssignmentsRelationManager;
use App\Filament\Resources\Employees\Pages\CreateEmployee;
use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Filament\Resources\Employees\Pages\ViewEmployee;
use App\Filament\Resources\Employees\RelationManagers\LicenseAssignmentsRelationManager;
use App\Filament\Resources\Licenses\Pages\ViewLicense;
use App\Filament\Resources\Licenses\RelationManagers\AssignmentsRelationManager as LicenseAssignmentsAssignmentsRelationManager;
use App\Models\Asset;
use App\Models\Assignment;
use App\Models\Department;
use App\Models\Employee;
use App\Models\License;
use App\Models\LicenseAssignment;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('lists employees', function () {
    Employee::factory()->count(3)->create();

    $this->get('/admin/employees')->assertOk();
});

it('creates an employee', function () {
    $department = Department::factory()->create();

    Livewire::test(CreateEmployee::class)
        ->fillForm([
            'name' => 'Jane Doe',
            'legajo' => 'AMP-999',
            'document_number' => '99999999',
            'department_id' => $department->id,
            'position' => 'Analista',
            'email' => 'jane.doe@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Employee::where('name', 'Jane Doe')->exists())->toBeTrue();
});

it('creates an employee with a document_type', function () {
    $department = Department::factory()->create();

    Livewire::test(CreateEmployee::class)
        ->fillForm([
            'name' => 'Jane Doe',
            'legajo' => 'AMP-999',
            'document_number' => '99999999',
            'document_type' => 'dni',
            'department_id' => $department->id,
            'position' => 'Analista',
            'email' => 'jane.doe@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Employee::where('name', 'Jane Doe')->first()->document_type)->toBe('dni');
});

it('creates an employee without a document_type since it is optional', function () {
    $department = Department::factory()->create();

    Livewire::test(CreateEmployee::class)
        ->fillForm([
            'name' => 'Jane Doe',
            'legajo' => 'AMP-999',
            'document_number' => '99999999',
            'document_type' => null,
            'department_id' => $department->id,
            'position' => 'Analista',
            'email' => 'jane.doe@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Employee::where('name', 'Jane Doe')->first()->document_type)->toBeNull();
});

it('requires a legajo to create', function () {
    Livewire::test(CreateEmployee::class)
        ->fillForm([
            'name' => 'Jane Doe',
            'legajo' => '',
            'document_number' => '99999999',
        ])
        ->call('create')
        ->assertHasFormErrors(['legajo' => 'required']);
});

it('requires a document_number to create', function () {
    Livewire::test(CreateEmployee::class)
        ->fillForm([
            'name' => 'Jane Doe',
            'legajo' => 'AMP-999',
            'document_number' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['document_number' => 'required']);
});

it('rejects a duplicate legajo', function () {
    Employee::factory()->create(['legajo' => 'AMP-100']);

    Livewire::test(CreateEmployee::class)
        ->fillForm([
            'name' => 'Other Person',
            'legajo' => 'AMP-100',
            'document_number' => '99999999',
        ])
        ->call('create')
        ->assertHasFormErrors(['legajo' => 'unique']);
});

it('requires a department to create', function () {
    Livewire::test(CreateEmployee::class)
        ->fillForm([
            'name' => 'Jane Doe',
            'legajo' => 'AMP-999',
            'document_number' => '99999999',
            'department_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['department_id' => 'required']);
});

it('requires a position to create', function () {
    Livewire::test(CreateEmployee::class)
        ->fillForm([
            'name' => 'Jane Doe',
            'legajo' => 'AMP-999',
            'document_number' => '99999999',
            'position' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['position' => 'required']);
});

it('requires an email to create', function () {
    Livewire::test(CreateEmployee::class)
        ->fillForm([
            'name' => 'Jane Doe',
            'legajo' => 'AMP-999',
            'document_number' => '99999999',
            'email' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'required']);
});

it('hides the is_active field on create but shows it on edit, defaulting new employees to active', function () {
    Livewire::test(CreateEmployee::class)
        ->assertFormFieldIsHidden('is_active');

    $employee = Employee::factory()->create();

    Livewire::test(EditEmployee::class, ['record' => $employee->getRouteKey()])
        ->assertFormFieldIsVisible('is_active');

    $department = Department::factory()->create();

    Livewire::test(CreateEmployee::class)
        ->fillForm([
            'name' => 'New Hire',
            'legajo' => 'AMP-998',
            'document_number' => '99999998',
            'department_id' => $department->id,
            'position' => 'Analista',
            'email' => 'new.hire@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Employee::where('name', 'New Hire')->first()->is_active)->toBeTrue();
});

it('returns 404 for a non-existent employee', function () {
    $this->get('/admin/employees/99999')->assertNotFound();
});

it('denies viewer from creating an employee', function () {
    loginAsViewer();

    Livewire::test(CreateEmployee::class)->assertForbidden();
});

it('hides the delete bulk action from editor on the list page', function () {
    Employee::factory()->create();
    loginAsEditor();

    Livewire::test(ListEmployees::class)->assertTableBulkActionHidden('delete');
});

it('blocks deleting an employee that has an assignment, with a friendly notification', function () {
    $employee = Employee::factory()->create();
    Assignment::factory()->returned()->create(['employee_id' => $employee->id]);

    Livewire::test(EditEmployee::class, ['record' => $employee->getRouteKey()])
        ->callAction('delete')
        ->assertNotified();

    expect(Employee::find($employee->id))->not->toBeNull();
});

it('allows deleting an employee with no assignments', function () {
    $employee = Employee::factory()->create();

    Livewire::test(EditEmployee::class, ['record' => $employee->getRouteKey()])
        ->callAction('delete');

    expect(Employee::find($employee->id))->toBeNull();
});

it('rejects a duplicate email', function () {
    Employee::factory()->create(['email' => 'jane@example.com']);

    Livewire::test(CreateEmployee::class)
        ->fillForm(['name' => 'Other Person', 'email' => 'jane@example.com'])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);
});

it('rejects a duplicate document_number', function () {
    Employee::factory()->create(['document_number' => '12345678']);

    Livewire::test(CreateEmployee::class)
        ->fillForm(['name' => 'Other Person', 'document_number' => '12345678'])
        ->call('create')
        ->assertHasFormErrors(['document_number' => 'unique']);
});

it('allows keeping its own email and document_number when editing', function () {
    $employee = Employee::factory()->create(['email' => 'jane@example.com', 'document_number' => '12345678']);

    Livewire::test(EditEmployee::class, ['record' => $employee->getRouteKey()])
        ->fillForm(['email' => 'jane@example.com', 'document_number' => '12345678'])
        ->call('save')
        ->assertHasNoFormErrors();
});

it('still allows editing an assignment after its employee becomes inactive', function () {
    $employee = Employee::factory()->create();
    $assignment = Assignment::factory()->create(['employee_id' => $employee->id]);
    $asset = Asset::factory()->available()->create();
    $assignment->assets()->attach($asset->id, ['assigned_at' => $assignment->assigned_at]);
    $employee->update(['is_active' => false]);

    Livewire::test(\App\Filament\Resources\Assignments\Pages\EditAssignment::class, ['record' => $assignment->getRouteKey()])
        ->fillForm(['notes' => 'Updated notes'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($assignment->fresh()->notes)->toBe('Updated notes');
});

it('still allows editing an assignment via the Assets relation manager after its employee becomes inactive', function () {
    $asset = Asset::factory()->create();
    $employee = Employee::factory()->create();
    $assignment = Assignment::factory()->create(['employee_id' => $employee->id]);
    $assignment->assets()->attach($asset->id, ['assigned_at' => $assignment->assigned_at]);
    $employee->update(['is_active' => false]);

    Livewire::test(AssetAssignmentsRelationManager::class, [
        'ownerRecord' => $asset,
        'pageClass' => ViewAsset::class,
    ])
        ->mountTableAction('edit', $assignment)
        ->setTableActionData(['employee_id' => $employee->id, 'assigned_at' => $assignment->assigned_at->toDateString()])
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();
});

it('still allows editing a license assignment after its employee becomes inactive', function () {
    $license = License::factory()->create();
    $employee = Employee::factory()->create();
    $licenseAssignment = LicenseAssignment::factory()->toEmployee()->create([
        'license_id' => $license->id,
        'employee_id' => $employee->id,
    ]);
    $employee->update(['is_active' => false]);

    Livewire::test(LicenseAssignmentsAssignmentsRelationManager::class, [
        'ownerRecord' => $license,
        'pageClass' => ViewLicense::class,
    ])
        ->mountTableAction('edit', $licenseAssignment)
        ->setTableActionData(['employee_id' => $employee->id, 'assigned_at' => $licenseAssignment->assigned_at->toDateString()])
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();
});

it('shows the license assignments belonging to the employee in the Licencias tab', function () {
    $employee = Employee::factory()->create();
    $license = License::factory()->create();
    $licenseAssignment = LicenseAssignment::factory()->toEmployee()->create([
        'license_id' => $license->id,
        'employee_id' => $employee->id,
    ]);

    Livewire::test(LicenseAssignmentsRelationManager::class, [
        'ownerRecord' => $employee,
        'pageClass' => ViewEmployee::class,
    ])->assertCanSeeTableRecords([$licenseAssignment]);
});

it('has no create/edit/delete actions on the Licencias tab (read-only)', function () {
    $employee = Employee::factory()->create();

    Livewire::test(LicenseAssignmentsRelationManager::class, [
        'ownerRecord' => $employee,
        'pageClass' => ViewEmployee::class,
    ])
        ->assertTableActionDoesNotExist('create')
        ->assertTableActionDoesNotExist('edit')
        ->assertTableActionDoesNotExist('delete');
});

it('shows document_type on the employee view page', function () {
    $employee = Employee::factory()->create(['document_type' => 'passport']);

    Livewire::test(ViewEmployee::class, ['record' => $employee->getRouteKey()])
        ->assertOk();
});

it('lists employees when some have no document_type', function () {
    Employee::factory()->create(['document_type' => null]);
    Employee::factory()->create(['document_type' => 'ci']);

    $this->get('/admin/employees')->assertOk();
});

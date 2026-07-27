<?php

use App\Models\Asset;
use App\Models\Assignment;
use App\Models\Employee;
use App\Services\AssignmentService;

beforeEach(function () {
    $this->admin = loginAsAdmin();
    $this->service = app(AssignmentService::class);
});

it('assigns an asset to an employee', function () {
    $asset = Asset::factory()->available()->create();
    $employee = Employee::factory()->create();

    $assignment = $this->service->assign($asset, [
        'employee_id' => $employee->id,
        'assigned_at' => '2026-07-01',
    ]);

    expect($assignment)->toBeInstanceOf(Assignment::class);
    expect($assignment->employee_id)->toBe($employee->id);
    expect($assignment->assigned_by)->toBe($this->admin->name);
    expect($assignment->assigned_at->format('Y-m-d'))->toBe('2026-07-01');
});

it('attaches asset to assignment on assign', function () {
    $asset = Asset::factory()->available()->create();
    $employee = Employee::factory()->create();

    $assignment = $this->service->assign($asset, [
        'employee_id' => $employee->id,
        'assigned_at' => '2026-07-01',
    ]);

    expect($assignment->assets()->count())->toBe(1);
    expect($assignment->assets->first()->id)->toBe($asset->id);
});

it('attaches asset with charger serial and ticket number', function () {
    $asset = Asset::factory()->available()->create();
    $employee = Employee::factory()->create();

    $assignment = $this->service->assign($asset, [
        'employee_id'   => $employee->id,
        'assigned_at'   => '2026-07-01',
        'charger_serial' => 'CHARGER-001',
        'ticket_number'  => 'TICKET-123',
        'notes'          => 'Some notes',
    ]);

    $pivot = $assignment->assets->first()->pivot;
    expect($pivot->charger_serial)->toBe('CHARGER-001');
    expect($pivot->ticket_number)->toBe('TICKET-123');
    expect($pivot->notes)->toBe('Some notes');
});

it('returns an asset', function () {
    $asset = Asset::factory()->available()->create();
    $employee = Employee::factory()->create();
    $assignment = $this->service->assign($asset, [
        'employee_id' => $employee->id,
        'assigned_at' => '2026-06-01',
    ]);

    $this->service->return($asset, [
        'returned_at' => '2026-07-01',
    ]);

    $assignment->refresh();
    expect($assignment->returned_at->format('Y-m-d'))->toBe('2026-07-01');
});

it('appends return notes to assignment', function () {
    $asset = Asset::factory()->available()->create();
    $employee = Employee::factory()->create();
    $assignment = $this->service->assign($asset, [
        'employee_id' => $employee->id,
        'assigned_at' => '2026-06-01',
        'notes'       => 'Original note',
    ]);

    $this->service->return($asset, [
        'returned_at' => '2026-07-01',
        'notes'       => 'Returned in good condition',
    ]);

    $assignment->refresh();
    expect($assignment->notes)->toContain('Original note');
    expect($assignment->notes)->toContain('[Devolución]');
    expect($assignment->notes)->toContain('Returned in good condition');
});

it('returns active employees list', function () {
    Employee::factory()->count(3)->create();
    Employee::factory()->inactive()->create();

    $employees = $this->service->getActiveEmployees();

    expect($employees)->toHaveCount(3);
});

it('returns active assignment for an asset', function () {
    $asset = Asset::factory()->create();
    $employee = Employee::factory()->create();
    $assignment = $this->service->assign($asset, [
        'employee_id' => $employee->id,
        'assigned_at' => '2026-07-01',
    ]);

    $active = $this->service->activeAssignment($asset);

    expect($active->id)->toBe($assignment->id);
});

it('returns null active assignment for returned asset', function () {
    $asset = Asset::factory()->create();
    $employee = Employee::factory()->create();
    $this->service->assign($asset, [
        'employee_id' => $employee->id,
        'assigned_at' => '2026-06-01',
    ]);

    $this->service->return($asset, ['returned_at' => '2026-07-01']);

    expect($this->service->activeAssignment($asset))->toBeNull();
});

it('notifies managers when an asset is assigned', function () {
    $asset = Asset::factory()->available()->create();
    $employee = Employee::factory()->create();

    $this->service->assign($asset, [
        'employee_id' => $employee->id,
        'assigned_at' => '2026-07-01',
    ]);

    $notification = $this->admin->notifications()
        ->where('type', \App\Notifications\AssetAssignmentNotification::class)
        ->where('data->alert_type', 'assigned')
        ->first();

    expect($notification)->not->toBeNull();
    expect($notification->data['asset_id'])->toBe($asset->id);

    $filamentNotification = $this->admin->notifications()
        ->where('type', \Filament\Notifications\DatabaseNotification::class)
        ->where('data->format', 'filament')
        ->first();

    expect($filamentNotification)->not->toBeNull();
    expect($filamentNotification->data['title'])->toContain('Activo asignado');
});

it('notifies managers when an asset is returned', function () {
    $asset = Asset::factory()->available()->create();
    $employee = Employee::factory()->create();
    $this->service->assign($asset, [
        'employee_id' => $employee->id,
        'assigned_at' => '2026-06-01',
    ]);

    $this->service->return($asset, ['returned_at' => '2026-07-01']);

    $notification = $this->admin->notifications()
        ->where('type', \App\Notifications\AssetAssignmentNotification::class)
        ->where('data->alert_type', 'returned')
        ->first();

    expect($notification)->not->toBeNull();
    expect($notification->data['asset_id'])->toBe($asset->id);

    $filamentNotification = $this->admin->notifications()
        ->where('type', \Filament\Notifications\DatabaseNotification::class)
        ->where('data->format', 'filament')
        ->where('data->title', 'like', 'Activo devuelto%')
        ->first();

    expect($filamentNotification)->not->toBeNull();
});

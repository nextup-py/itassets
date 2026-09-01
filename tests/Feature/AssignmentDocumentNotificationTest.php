<?php

use App\Filament\Resources\Assignments\Pages\CreateAssignment;
use App\Models\Asset;
use App\Models\Employee;
use App\Models\Setting;
use App\Notifications\AssignmentDocumentNotification;
use App\Services\AssignmentService;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('emails the employee the assignment document when a new assignment is created', function () {
    Notification::fake();

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

    Notification::assertSentTo($employee, AssignmentDocumentNotification::class);
});

it('does not email the employee when the setting is disabled', function () {
    Setting::set('notify_employee_on_assignment', false);
    Notification::fake();

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

    Notification::assertNotSentTo($employee, AssignmentDocumentNotification::class);
});

it('emails the employee when assigned via the quick single-asset action', function () {
    Notification::fake();

    $employee = Employee::factory()->create();
    $asset = Asset::factory()->available()->create();

    app(AssignmentService::class)->assign($asset, [
        'employee_id' => $employee->id,
        'assigned_at' => now()->toDateString(),
    ]);

    Notification::assertSentTo($employee, AssignmentDocumentNotification::class);
});

it('attaches a pdf with the mail notification', function () {
    $employee = Employee::factory()->create();
    $asset = Asset::factory()->available()->create();

    $assignment = app(AssignmentService::class)->assign($asset, [
        'employee_id' => $employee->id,
        'assigned_at' => now()->toDateString(),
    ]);

    $mailMessage = (new AssignmentDocumentNotification($assignment->loadMissing('employee', 'assets')))->toMail($employee);

    expect($mailMessage->rawAttachments)->toHaveCount(1);
    expect($mailMessage->rawAttachments[0]['options']['mime'])->toBe('application/pdf');
});

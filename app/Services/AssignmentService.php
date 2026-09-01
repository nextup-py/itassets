<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Assignment;
use App\Models\Employee;
use App\Models\Setting;
use App\Notifications\AssignmentDocumentNotification;
use Illuminate\Contracts\Auth\Factory as Auth;

class AssignmentService
{
    public function __construct(
        private Auth $auth,
    ) {}

    public function assign(Asset $asset, array $data): Assignment
    {
        $assignment = Assignment::create([
            'employee_id' => $data['employee_id'],
            'assigned_by' => $this->auth->user()?->name,
            'assigned_at' => $data['assigned_at'],
            'notes'       => $data['notes'] ?? null,
        ]);

        $assignment->assets()->attach($asset->id, [
            'charger_serial' => $data['charger_serial'] ?? null,
            'ticket_number'  => $data['ticket_number'] ?? null,
            'assigned_at'    => $data['assigned_at'],
            'notes'          => $data['notes'] ?? null,
        ]);

        $asset->refresh();

        $this->notifyEmployee($assignment);

        return $assignment;
    }

    public function notifyEmployee(Assignment $assignment): void
    {
        if (! Setting::get('notify_employee_on_assignment', true)) {
            return;
        }

        $assignment->loadMissing('employee', 'assets.category');

        if (! $assignment->employee) {
            return;
        }

        $assignment->employee->notify(new AssignmentDocumentNotification($assignment));
    }

    public function return(Asset $asset, array $data): void
    {
        $active = $asset->activeAssignment();

        if ($active) {
            $notes = $active->notes;
            if (! empty($data['notes'])) {
                $notes = $notes
                    ? $notes . "\n[Devolución] " . $data['notes']
                    : '[Devolución] ' . $data['notes'];
            }

            $active->update([
                'returned_at' => $data['returned_at'],
                'notes'       => $notes,
            ]);
        }

        $asset->refresh();
    }

    public function getActiveEmployees(): array
    {
        return Employee::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function activeAssignment(Asset $asset): ?Assignment
    {
        return $asset->activeAssignment();
    }
}

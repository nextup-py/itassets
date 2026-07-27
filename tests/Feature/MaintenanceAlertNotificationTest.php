<?php

use App\Models\MaintenanceRecord;
use App\Notifications\MaintenanceAlertNotification;
use App\Models\User;

it('has correct structure for prolonged maintenance', function () {
    $record = MaintenanceRecord::factory()->inProgress()->create();

    $notification = new MaintenanceAlertNotification($record, 'prolonged');
    $array = $notification->toArray(new User);

    expect($array['maintenance_id'])->toBe($record->id);
    expect($array['asset_tag'])->not->toBeNull();
    expect($array['alert_type'])->toBe('prolonged');
    expect($array['message'])->toContain('Mantenimiento prolongado');
});

<?php

use App\Models\Asset;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\AssetAssignmentNotification;

it('has correct structure for an assigned asset', function () {
    $asset = Asset::factory()->create(['asset_tag' => 'IT-0099', 'name' => 'Test Asset']);
    $employee = Employee::factory()->create(['name' => 'Carlos Gómez']);

    $notification = new AssetAssignmentNotification($asset, 'assigned', $employee);
    $array = $notification->toArray(new User);

    expect($array['asset_id'])->toBe($asset->id);
    expect($array['employee'])->toBe('Carlos Gómez');
    expect($array['alert_type'])->toBe('assigned');
    expect($array['message'])->toContain('Activo asignado');
});

it('has correct structure for a returned asset', function () {
    $asset = Asset::factory()->create(['asset_tag' => 'IT-0099', 'name' => 'Test Asset']);
    $employee = Employee::factory()->create(['name' => 'Carlos Gómez']);

    $notification = new AssetAssignmentNotification($asset, 'returned', $employee);
    $array = $notification->toArray(new User);

    expect($array['alert_type'])->toBe('returned');
    expect($array['message'])->toContain('Activo devuelto');
});

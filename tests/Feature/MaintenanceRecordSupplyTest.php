<?php

use App\Models\Asset;
use App\Models\MaintenanceRecord;
use App\Models\Supply;

it('decreases supply stock when linked to a maintenance record', function () {
    $supply = Supply::factory()->create(['quantity_available' => 10]);
    $record = MaintenanceRecord::factory()->for(Asset::factory())->create();

    $record->supplies()->attach($supply->id, ['quantity_used' => 3]);

    expect($supply->fresh()->quantity_available)->toBe(7);
});

it('restores supply stock when detached from a maintenance record', function () {
    $supply = Supply::factory()->create(['quantity_available' => 10]);
    $record = MaintenanceRecord::factory()->for(Asset::factory())->create();

    $record->supplies()->attach($supply->id, ['quantity_used' => 3]);
    $record->supplies()->detach($supply->id);

    expect($supply->fresh()->quantity_available)->toBe(10);
});

it('refuses to consume more supply stock than is available', function () {
    $supply = Supply::factory()->create(['quantity_available' => 2]);
    $record = MaintenanceRecord::factory()->for(Asset::factory())->create();

    $record->supplies()->attach($supply->id, ['quantity_used' => 5]);
})->throws(RuntimeException::class);

it('restores supply stock when the maintenance record itself is deleted', function () {
    $supply = Supply::factory()->create(['quantity_available' => 10]);
    $record = MaintenanceRecord::factory()->for(Asset::factory())->create();

    $record->supplies()->attach($supply->id, ['quantity_used' => 3]);
    expect($supply->fresh()->quantity_available)->toBe(7);

    $record->delete();

    expect($supply->fresh()->quantity_available)->toBe(10);
    expect(\DB::table('maintenance_record_supply')->where('maintenance_record_id', $record->id)->count())->toBe(0);
});

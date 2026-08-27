<?php

use App\Models\Supply;
use App\Models\SupplyAdjustment;
use App\Services\SupplyAdjustmentService;

it('decreases stock and records an auditable adjustment', function () {
    $supply = Supply::factory()->create(['quantity_available' => 10]);
    $admin = loginAsAdmin();

    $adjustment = app(SupplyAdjustmentService::class)->register($supply, [
        'quantity' => 3,
        'reason' => 'loss',
        'notes' => 'Extraviado en mudanza de oficina',
    ]);

    expect($supply->fresh()->quantity_available)->toBe(7)
        ->and($adjustment)->toBeInstanceOf(SupplyAdjustment::class)
        ->and($adjustment->reason)->toBe('loss')
        ->and($adjustment->quantity)->toBe(3)
        ->and($adjustment->created_by)->toBe($admin->id);
});

it('refuses to adjust more than the available stock', function () {
    $supply = Supply::factory()->create(['quantity_available' => 2]);

    app(SupplyAdjustmentService::class)->register($supply, [
        'quantity' => 5,
        'reason' => 'damage',
    ]);
})->throws(RuntimeException::class);

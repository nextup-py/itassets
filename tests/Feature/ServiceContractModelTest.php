<?php

use App\Models\Asset;
use App\Models\ServiceContract;
use App\Models\Supplier;

beforeEach(function () {
    loginAsAdmin();
});

it('has contract types defined', function () {
    expect(ServiceContract::TYPES)->toHaveKeys(['maintenance', 'support', 'extended_warranty', 'consulting']);
});

it('has billing cycles defined', function () {
    expect(ServiceContract::BILLING_CYCLES)->toHaveKeys(['one_time', 'monthly', 'annual']);
});

it('returns the type label', function () {
    $contract = ServiceContract::factory()->create(['contract_type' => 'support']);

    expect($contract->getTypeLabel())->toBe('Soporte');
});

it('belongs to a supplier', function () {
    $supplier = Supplier::factory()->create();
    $contract = ServiceContract::factory()->create(['supplier_id' => $supplier->id]);

    expect($contract->supplier->is($supplier))->toBeTrue();
});

it('can cover zero, one, or multiple assets', function () {
    $contract = ServiceContract::factory()->create();
    $assets = Asset::factory()->count(2)->create();

    expect($contract->assets)->toHaveCount(0);

    $contract->assets()->attach($assets->pluck('id'));

    expect($contract->fresh()->assets)->toHaveCount(2)
        ->and($assets->first()->fresh()->serviceContracts)->toHaveCount(1);
});

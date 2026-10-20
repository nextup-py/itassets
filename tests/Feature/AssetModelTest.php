<?php

use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Database\UniqueConstraintViolationException;

beforeEach(function () {
    loginAsAdmin();
});

it('auto-generates asset_tag when not provided', function () {
    $asset = Asset::factory()->create(['asset_tag' => null]);

    expect($asset->asset_tag)->toMatch('/^IT-\d{4}$/');
});

it('increments asset_tag sequentially', function () {
    Asset::factory()->create(['asset_tag' => 'IT-0005']);
    $asset = Asset::factory()->create(['asset_tag' => null]);

    expect($asset->asset_tag)->toBe('IT-0006');
});

it('uses provided asset_tag', function () {
    $asset = Asset::factory()->create(['asset_tag' => 'CUSTOM-001']);

    expect($asset->asset_tag)->toBe('CUSTOM-001');
});

it('retries asset_tag generation when a concurrent insert takes the computed tag first', function () {
    Asset::factory()->create(['asset_tag' => 'IT-0001']);

    // Simulates another request inserting IT-0002 between this asset's tag
    // computation and its own insert, forcing a real unique-constraint hit.
    $listener = Asset::creating(function (Asset $asset) {
        if ($asset->asset_tag === 'IT-0002') {
            Asset::withoutEvents(fn () => Asset::factory()->create(['asset_tag' => 'IT-0002']));
        }
    });

    $asset = Asset::factory()->create(['asset_tag' => null]);

    expect($asset->asset_tag)->toBe('IT-0003');
});

it('does not retry when an explicit asset_tag collides', function () {
    Asset::factory()->create(['asset_tag' => 'CUSTOM-001']);

    expect(fn () => Asset::factory()->create(['asset_tag' => 'CUSTOM-001']))
        ->toThrow(UniqueConstraintViolationException::class);
});

it('has available scope', function () {
    Asset::factory()->count(3)->available()->create();
    Asset::factory()->assigned()->create();

    expect(Asset::where('status', 'available')->count())->toBe(3);
});

it('returns correct status label', function () {
    $asset = Asset::factory()->available()->create(['status' => 'available']);

    expect($asset->getStatusLabel())->toBe('Disponible');
});

it('belongs to a category', function () {
    $category = AssetCategory::factory()->create();
    $asset = Asset::factory()->create(['asset_category_id' => $category->id]);

    expect($asset->category)->toBeInstanceOf(AssetCategory::class);
    expect($asset->category->id)->toBe($category->id);
});

it('has status constants defined', function () {
    expect(Asset::STATUSES)->toHaveKeys(['stock', 'available', 'assigned', 'maintenance', 'retired', 'lost']);
});

it('has condition constants defined', function () {
    expect(Asset::CONDITIONS)->toHaveKeys(['new', 'good', 'fair', 'poor']);
});

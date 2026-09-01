<?php

use App\Models\Asset;
use App\Support\AssetQrCode;

it('builds the public url for an asset', function () {
    $asset = Asset::factory()->create();

    expect(AssetQrCode::url($asset))->toBe(route('assets.public', $asset));
});

it('generates a base64 png data uri', function () {
    $asset = Asset::factory()->create();

    $dataUri = AssetQrCode::dataUri($asset);

    expect($dataUri)->toStartWith('data:image/png;base64,');
});

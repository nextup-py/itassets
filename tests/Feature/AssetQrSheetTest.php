<?php

use App\Models\Asset;
use Illuminate\Support\Facades\Cache;

it('requires authentication to view the qr sheet', function () {
    $response = $this->get(route('assets.qr-sheet', 'any-token'));

    $response->assertStatus(302);
});

it('denies a viewer from accessing the qr sheet', function () {
    loginAsViewer();

    $response = $this->get(route('assets.qr-sheet', 'any-token'));

    $response->assertForbidden();
});

it('denies an editor from accessing the qr sheet', function () {
    loginAsEditor();

    $response = $this->get(route('assets.qr-sheet', 'any-token'));

    $response->assertForbidden();
});

it('returns 410 for an unknown or expired token', function () {
    loginAsAdmin();

    $response = $this->get(route('assets.qr-sheet', 'unknown-token'));

    $response->assertStatus(410);
});

it('renders a pdf sheet with the cached assets for an admin', function () {
    loginAsAdmin();

    $assets = Asset::factory()->count(2)->create();
    $token = 'test-token';
    Cache::put("qr_sheet.{$token}", $assets->pluck('id')->all(), now()->addMinutes(5));

    $response = $this->get(route('assets.qr-sheet', $token));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

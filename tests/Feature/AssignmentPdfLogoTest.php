<?php

use App\Models\Assignment;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    loginAsAdmin();
});

it('renders the pdf without a logo image when none is configured', function () {
    $assignment = Assignment::factory()->create();

    $html = view('pdf.assignment', ['assignment' => $assignment->loadMissing('employee', 'assets')])->render();

    expect($html)->not->toContain('<img');
});

it('renders the pdf with the configured company logo embedded as a data uri', function () {
    Storage::fake('public');

    $logo = UploadedFile::fake()->image('logo.png');
    $path = $logo->store('branding', 'public');
    Setting::set('company_logo', $path);

    $assignment = Assignment::factory()->create();

    $html = view('pdf.assignment', ['assignment' => $assignment->loadMissing('employee', 'assets')])->render();

    expect($html)->toContain('<img src="data:image/png;base64,');
});

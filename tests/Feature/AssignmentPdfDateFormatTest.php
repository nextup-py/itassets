<?php

use App\Models\Assignment;
use App\Models\Setting;

beforeEach(function () {
    loginAsAdmin();
});

it('renders the pdf footer timestamp using the configured date_format', function () {
    Setting::set('date_format', 'Y-m-d');

    $assignment = Assignment::factory()->create();

    $html = view('pdf.assignment', ['assignment' => $assignment->loadMissing('employee', 'assets')])->render();

    expect($html)->toContain('Documento generado el ' . now()->format('Y-m-d H:i'));
});

it('falls back to d/m/Y H:i in the pdf footer when no date_format setting exists', function () {
    $assignment = Assignment::factory()->create();

    $html = view('pdf.assignment', ['assignment' => $assignment->loadMissing('employee', 'assets')])->render();

    expect($html)->toContain('Documento generado el ' . now()->format('d/m/Y H:i'));
});

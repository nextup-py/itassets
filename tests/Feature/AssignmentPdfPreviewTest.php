<?php

use App\Models\Assignment;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

it('requires authentication to view a pdf preview', function () {
    $response = $this->get(route('assignments.pdf-preview', 'any-token'));

    $response->assertStatus(302);
});

it('denies a viewer from accessing a pdf preview', function () {
    loginAsViewer();

    $response = $this->get(route('assignments.pdf-preview', 'any-token'));

    $response->assertForbidden();
});

it('returns 410 for an unknown or expired preview token', function () {
    loginAsAdmin();

    $response = $this->get(route('assignments.pdf-preview', 'unknown-token'));

    $response->assertStatus(410);
});

it('renders a sample pdf using the cached override data', function () {
    loginAsAdmin();

    $token = 'test-token';
    Cache::put("pdf_preview.{$token}", [
        'company_name' => 'Acme Corp',
        'pdf_title' => 'Acta de Entrega de Equipos',
        'pdf_intro' => 'Intro de prueba',
        'pdf_clauses' => ['Cláusula de prueba'],
        'pdf_closing' => 'Cierre de prueba',
    ], now()->addMinutes(2));

    $response = $this->get(route('assignments.pdf-preview', $token));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

it('lets pdf overrides take precedence over saved settings', function () {
    Setting::set('company_name', 'Saved Corp');
    Setting::set('pdf_title', 'Saved Title');

    $assignment = Assignment::factory()->create();

    $html = view('pdf.assignment', [
        'assignment' => $assignment->loadMissing('employee', 'assets'),
        'overrides' => [
            'company_name' => 'Override Corp',
            'pdf_title' => 'Override Title',
        ],
    ])->render();

    expect($html)->toContain('Override Title')
        ->and($html)->not->toContain('Saved Title');
});

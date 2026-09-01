<?php

use App\Filament\Pages\PdfSettings;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('saves the pdf text settings', function () {
    Livewire::test(PdfSettings::class)
        ->fillForm([
            'company_name' => 'Acme Corp',
            'pdf_title' => 'Acta de Entrega de Equipos',
            'pdf_intro' => 'Intro text for :company',
            'pdf_clauses' => [
                ['clause' => 'First clause'],
                ['clause' => 'Second clause'],
            ],
            'pdf_closing' => 'Closing text',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::get('company_name'))->toBe('Acme Corp');
    expect(Setting::get('pdf_title'))->toBe('Acta de Entrega de Equipos');
    expect(Setting::get('pdf_intro'))->toBe('Intro text for :company');
    expect(Setting::get('pdf_clauses'))->toBe(['First clause', 'Second clause']);
    expect(Setting::get('pdf_closing'))->toBe('Closing text');
});

it('uploads and saves a company logo', function () {
    Storage::fake('public');

    $logo = UploadedFile::fake()->image('logo.png');

    Livewire::test(PdfSettings::class)
        ->fillForm([
            'company_name' => 'Acme Corp',
            'company_logo' => $logo,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $storedPath = Setting::get('company_logo');

    expect($storedPath)->not->toBeNull();
    Storage::disk('public')->assertExists($storedPath);
});

it('denies viewer access', function () {
    loginAsViewer();

    Livewire::test(PdfSettings::class)->assertForbidden();
});

it('allows editor access', function () {
    loginAsEditor();

    Livewire::test(PdfSettings::class)->assertSuccessful();
});

it('renders the save button inside a real form so the submit actually works', function () {
    $this->get('/admin/pdf-settings')->assertSee('wire:submit="save"', false);
});

it('caches the current unsaved form state when previewing, without saving it', function () {
    Setting::set('pdf_title', 'Saved Title');
    Cache::spy();

    Livewire::test(PdfSettings::class)
        ->fillForm([
            'company_name' => 'Unsaved Corp',
            'pdf_title' => 'Unsaved Title',
        ])
        ->call('preview');

    Cache::shouldHaveReceived('put')->once()->withArgs(
        fn (string $key, array $value) => str_starts_with($key, 'pdf_preview.')
            && $value['company_name'] === 'Unsaved Corp'
            && $value['pdf_title'] === 'Unsaved Title'
    );

    expect(Setting::get('pdf_title'))->toBe('Saved Title');
});

<?php

use App\Filament\Pages\GeneralSettings;
use App\Models\Setting;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('saves all five regional settings', function () {
    Livewire::test(GeneralSettings::class)
        ->fillForm([
            'base_currency' => 'PYG',
            'display_currency' => 'USD',
            'exchange_rate' => 6500,
            'display_locale' => 'es_PY',
            'timezone' => 'America/Asuncion',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::get('base_currency'))->toBe('PYG');
    expect(Setting::get('display_currency'))->toBe('USD');
    expect((float) Setting::get('exchange_rate'))->toBe(6500.0);
    expect(Setting::get('display_locale'))->toBe('es_PY');
    expect(Setting::get('timezone'))->toBe('America/Asuncion');
});

it('saves regional settings with a different currency/locale/timezone combination', function () {
    Livewire::test(GeneralSettings::class)
        ->fillForm([
            'base_currency' => 'EUR',
            'display_currency' => 'GBP',
            'exchange_rate' => 0.86,
            'display_locale' => 'de_DE',
            'timezone' => 'Europe/Berlin',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::get('base_currency'))->toBe('EUR');
    expect(Setting::get('display_currency'))->toBe('GBP');
    expect((float) Setting::get('exchange_rate'))->toBe(0.86);
    expect(Setting::get('display_locale'))->toBe('de_DE');
    expect(Setting::get('timezone'))->toBe('Europe/Berlin');
});

it('denies viewer access', function () {
    loginAsViewer();

    Livewire::test(GeneralSettings::class)->assertForbidden();
});

it('allows editor access', function () {
    loginAsEditor();

    Livewire::test(GeneralSettings::class)->assertSuccessful();
});

it('rejects a base_currency that is not a 3-letter code', function () {
    Livewire::test(GeneralSettings::class)
        ->fillForm(['base_currency' => 'PY1'])
        ->call('save')
        ->assertHasFormErrors(['base_currency' => 'regex']);
});

it('rejects a display_currency that is not a 3-letter code', function () {
    Livewire::test(GeneralSettings::class)
        ->fillForm(['display_currency' => 'US$'])
        ->call('save')
        ->assertHasFormErrors(['display_currency' => 'regex']);
});

it('rejects a display_locale that does not match the xx_XX format', function () {
    Livewire::test(GeneralSettings::class)
        ->fillForm(['display_locale' => 'espy'])
        ->call('save')
        ->assertHasFormErrors(['display_locale' => 'regex']);
});

it('requires an exchange_rate', function () {
    Livewire::test(GeneralSettings::class)
        ->fillForm(['exchange_rate' => null])
        ->call('save')
        ->assertHasFormErrors(['exchange_rate' => 'required']);
});

it('rejects an exchange_rate of 0', function () {
    Livewire::test(GeneralSettings::class)
        ->fillForm(['exchange_rate' => 0])
        ->call('save')
        ->assertHasFormErrors(['exchange_rate' => 'min']);
});

it('normalizes currency codes to uppercase when saving', function () {
    Livewire::test(GeneralSettings::class)
        ->fillForm([
            'base_currency' => 'pyg',
            'display_currency' => 'usd',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::get('base_currency'))->toBe('PYG');
    expect(Setting::get('display_currency'))->toBe('USD');
});

it('renders the save button inside a real form so the submit actually works', function () {
    $this->get('/admin/general-settings')->assertSee('wire:submit="save"', false);
});

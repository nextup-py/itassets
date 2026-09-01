<?php

use App\Filament\Pages\MailSettings;
use App\Mail\TestMail;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    loginAsAdmin();
});

it('denies editor access', function () {
    loginAsEditor();

    Livewire::test(MailSettings::class)->assertForbidden();
});

it('denies viewer access', function () {
    loginAsViewer();

    Livewire::test(MailSettings::class)->assertForbidden();
});

it('allows admin access', function () {
    Livewire::test(MailSettings::class)->assertSuccessful();
});

it('saves mail settings and encrypts the resend key', function () {
    Livewire::test(MailSettings::class)
        ->fillForm([
            'mail_mailer' => 'resend',
            'mail_from_address' => 'noreply@example.com',
            'mail_from_name' => 'ITAssets',
            'mail_resend_key' => 're_secret_key',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::get('mail_mailer'))->toBe('resend');
    expect(Setting::get('mail_from_address'))->toBe('noreply@example.com');

    $stored = Setting::get('mail_resend_key');
    expect($stored)->not->toBe('re_secret_key');
    expect(Crypt::decryptString($stored))->toBe('re_secret_key');
});

it('requires a resend key when the mailer is resend', function () {
    Livewire::test(MailSettings::class)
        ->fillForm([
            'mail_mailer' => 'resend',
            'mail_from_address' => 'noreply@example.com',
            'mail_from_name' => 'ITAssets',
            'mail_resend_key' => '',
        ])
        ->call('save')
        ->assertHasFormErrors(['mail_resend_key' => 'required']);
});

it('requires smtp host and port when the mailer is smtp', function () {
    Livewire::test(MailSettings::class)
        ->fillForm([
            'mail_mailer' => 'smtp',
            'mail_from_address' => 'noreply@example.com',
            'mail_from_name' => 'ITAssets',
            'mail_smtp_host' => '',
            'mail_smtp_port' => '',
        ])
        ->call('save')
        ->assertHasFormErrors(['mail_smtp_host' => 'required', 'mail_smtp_port' => 'required']);
});

it('sends a test email using the current unsaved form values', function () {
    Mail::fake();

    Livewire::test(MailSettings::class)
        ->fillForm([
            'mail_mailer' => 'resend',
            'mail_from_address' => 'noreply@example.com',
            'mail_from_name' => 'ITAssets',
            'mail_resend_key' => 're_unsaved_key',
        ])
        ->call('sendTest', 'destino@example.com');

    Mail::assertSent(TestMail::class, function (TestMail $mail) {
        return collect($mail->to)->pluck('address')->contains('destino@example.com');
    });

    // The setting must remain unsaved — sending a test never persists it.
    expect(Setting::get('mail_mailer'))->toBeNull();
});

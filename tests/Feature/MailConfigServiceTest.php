<?php

use App\Models\Setting;
use App\Services\MailConfigService;
use Illuminate\Support\Facades\Crypt;

it('leaves the default mail config untouched when no mail_mailer setting exists', function () {
    $originalDefault = config('mail.default');

    app(MailConfigService::class)->apply();

    expect(config('mail.default'))->toBe($originalDefault);
});

it('applies the saved resend key and from address at runtime', function () {
    Setting::set('mail_mailer', 'resend');
    Setting::set('mail_from_address', 'noreply@example.com');
    Setting::set('mail_from_name', 'ITAssets');
    Setting::set('mail_resend_key', Crypt::encryptString('re_stored_key'));

    app(MailConfigService::class)->apply();

    expect(config('mail.default'))->toBe('resend');
    expect(config('mail.from.address'))->toBe('noreply@example.com');
    expect(config('mail.mailers.resend.key'))->toBe('re_stored_key');
});

it('applies the saved smtp settings at runtime', function () {
    Setting::set('mail_mailer', 'smtp');
    Setting::set('mail_smtp_host', 'smtp.example.com');
    Setting::set('mail_smtp_port', 587);
    Setting::set('mail_smtp_password', Crypt::encryptString('smtp_secret'));

    app(MailConfigService::class)->apply();

    expect(config('mail.mailers.smtp.host'))->toBe('smtp.example.com');
    expect(config('mail.mailers.smtp.port'))->toBe(587);
    expect(config('mail.mailers.smtp.password'))->toBe('smtp_secret');
});

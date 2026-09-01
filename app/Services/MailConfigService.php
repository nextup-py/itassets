<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class MailConfigService
{
    // Lets MailSettings (Filament) override the .env-based mail config at
    // runtime — guarded so a pre-migration boot (e.g. the deploy's own
    // `artisan migrate`) never fails on a missing `settings` table.
    public function apply(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $mailer = Setting::get('mail_mailer');

        if (! $mailer) {
            return;
        }

        config(['mail.default' => $mailer]);
        config(['mail.from.address' => Setting::get('mail_from_address', config('mail.from.address'))]);
        config(['mail.from.name' => Setting::get('mail_from_name', config('mail.from.name'))]);

        match ($mailer) {
            'resend' => config(['mail.mailers.resend.key' => $this->decrypt('mail_resend_key')]),
            'smtp' => config([
                'mail.mailers.smtp.host' => Setting::get('mail_smtp_host'),
                'mail.mailers.smtp.port' => (int) Setting::get('mail_smtp_port'),
                'mail.mailers.smtp.username' => Setting::get('mail_smtp_username'),
                'mail.mailers.smtp.password' => $this->decrypt('mail_smtp_password'),
                'mail.mailers.smtp.encryption' => Setting::get('mail_smtp_encryption') ?: null,
            ]),
            default => null,
        };
    }

    protected function decrypt(string $key): ?string
    {
        $value = Setting::get($key);

        if (blank($value)) {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }
}

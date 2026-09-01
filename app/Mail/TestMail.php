<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function build(): static
    {
        return $this
            ->subject('Correo de prueba — ITAssets')
            ->text('mail.test');
    }
}

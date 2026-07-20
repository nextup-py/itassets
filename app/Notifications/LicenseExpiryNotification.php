<?php

namespace App\Notifications;

use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LicenseExpiryNotification extends Notification
{
    use Queueable;

    public function __construct(
        public License $license,
        public int $daysRemaining,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isExpired = $this->daysRemaining <= 0;
        $subject = $isExpired
            ? "Licencia vencida: {$this->license->product_name}"
            : "Licencia por vencer ({$this->daysRemaining} días): {$this->license->product_name}";

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hola, ' . $notifiable->name)
            ->line($isExpired
                ? "La licencia **{$this->license->product_name}** ha vencido."
                : "La licencia **{$this->license->product_name}** vencerá en {$this->daysRemaining} días."
            )
            ->line("Fecha de vencimiento: {$this->license->expiry_date?->format('d/m/Y')}")
            ->line("Asientos totales: {$this->license->total_seats}")
            ->line("Asientos utilizados: {$this->license->usedSeats()}")
            ->action('Ver licencia', url("/admin/licenses/{$this->license->id}"))
            ->salutation('ITAssets');
    }

    public function toArray(object $notifiable): array
    {
        $isExpired = $this->daysRemaining <= 0;

        return [
            'license_id'    => $this->license->id,
            'product_name'  => $this->license->product_name,
            'expiry_date'   => $this->license->expiry_date?->format('d/m/Y'),
            'days_remaining' => $this->daysRemaining,
            'type'          => $isExpired ? 'license_expired' : 'license_expiring',
            'message'       => $isExpired
                ? "Licencia vencida: {$this->license->product_name}"
                : "Licencia por vencer ({$this->daysRemaining} días): {$this->license->product_name}",
        ];
    }
}

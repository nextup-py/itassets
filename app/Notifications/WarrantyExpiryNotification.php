<?php

namespace App\Notifications;

use App\Models\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WarrantyExpiryNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Asset $asset,
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
            ? "Garantía vencida: {$this->asset->asset_tag} {$this->asset->name}"
            : "Garantía por vencer ({$this->daysRemaining} días): {$this->asset->asset_tag} {$this->asset->name}";

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hola, ' . $notifiable->name)
            ->line($isExpired
                ? "La garantía del activo **{$this->asset->name}** ({$this->asset->asset_tag}) ha vencido."
                : "La garantía del activo **{$this->asset->name}** ({$this->asset->asset_tag}) vencerá en {$this->daysRemaining} días."
            )
            ->line("Fecha de vencimiento: {$this->asset->warranty_expiry_date?->format('d/m/Y')}")
            ->when($this->asset->supplier, fn ($msg) => $msg->line("Proveedor: {$this->asset->supplier->name}"))
            ->action('Ver activo', url("/admin/assets/{$this->asset->id}"))
            ->salutation('ITAssets');
    }

    public function toArray(object $notifiable): array
    {
        $isExpired = $this->daysRemaining <= 0;

        return [
            'asset_id'      => $this->asset->id,
            'asset_tag'     => $this->asset->asset_tag,
            'asset_name'    => $this->asset->name,
            'expiry_date'   => $this->asset->warranty_expiry_date?->format('d/m/Y'),
            'days_remaining' => $this->daysRemaining,
            'type'          => $isExpired ? 'warranty_expired' : 'warranty_expiring',
            'message'       => $isExpired
                ? "Garantía vencida: {$this->asset->asset_tag} {$this->asset->name}"
                : "Garantía por vencer ({$this->daysRemaining} días): {$this->asset->asset_tag} {$this->asset->name}",
        ];
    }
}

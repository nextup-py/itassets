<?php

namespace App\Notifications;

use App\Filament\Resources\ServiceContracts\ServiceContractResource;
use App\Models\ServiceContract;
use App\Notifications\Concerns\SendsToManagers;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable, SendsToManagers;

    public function __construct(
        public ServiceContract $contract,
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
            ? "Contrato vencido: {$this->contract->name}"
            : "Contrato por vencer ({$this->daysRemaining} días): {$this->contract->name}";

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hola, ' . $notifiable->name)
            ->line($isExpired
                ? "El contrato **{$this->contract->name}** ha vencido."
                : "El contrato **{$this->contract->name}** vencerá en {$this->daysRemaining} días."
            )
            ->line("Proveedor: {$this->contract->supplier?->name}")
            ->line("Fecha de vencimiento: {$this->contract->expiry_date?->format(current_date_format())}")
            ->line('Renovación automática: ' . ($this->contract->auto_renew ? 'Sí' : 'No'))
            ->action('Ver contrato', ServiceContractResource::getUrl('view', ['record' => $this->contract]))
            ->salutation('ITAssets');
    }

    public function toArray(object $notifiable): array
    {
        $isExpired = $this->daysRemaining <= 0;

        return [
            'contract_id'    => $this->contract->id,
            'name'           => $this->contract->name,
            'expiry_date'    => $this->contract->expiry_date?->format(current_date_format()),
            'days_remaining' => $this->daysRemaining,
            'type'           => $isExpired ? 'contract_expired' : 'contract_expiring',
            'message'        => $isExpired
                ? "Contrato vencido: {$this->contract->name}"
                : "Contrato por vencer ({$this->daysRemaining} días): {$this->contract->name}",
        ];
    }

    public function toFilament(): FilamentNotification
    {
        $isExpired = $this->daysRemaining <= 0;

        return FilamentNotification::make()
            ->title($isExpired
                ? "Contrato vencido: {$this->contract->name}"
                : "Contrato por vencer ({$this->daysRemaining} días): {$this->contract->name}")
            ->body('Vencimiento: ' . ($this->contract->expiry_date?->format(current_date_format()) ?? '—'))
            ->status($isExpired ? 'danger' : 'warning');
    }
}

<?php

namespace App\Notifications;

use App\Models\MaintenanceRecord;
use App\Notifications\Concerns\SendsToManagers;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceAlertNotification extends Notification implements ShouldQueue
{
    use Queueable, SendsToManagers;

    public function __construct(
        public MaintenanceRecord $record,
        public string $alertType,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $assetTag = $this->record->asset?->asset_tag ?? 'N/A';
        $assetName = $this->record->asset?->name ?? 'N/A';

        $subject = match ($this->alertType) {
            'prolonged' => "Mantenimiento prolongado: {$assetTag} {$assetName}",
            'started'   => "Mantenimiento iniciado: {$assetTag} {$assetName}",
            default     => "Alerta de mantenimiento: {$assetTag} {$assetName}",
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hola, ' . $notifiable->name)
            ->line(match ($this->alertType) {
                'prolonged' => "El mantenimiento del activo **{$assetName}** ({$assetTag}) lleva más de 7 días en curso.",
                'started'   => "Se inició un mantenimiento para el activo **{$assetName}** ({$assetTag}).",
                default     => "Alerta de mantenimiento para el activo **{$assetName}** ({$assetTag}).",
            })
            ->line("Tipo: {$this->record->type}")
            ->line("Estado: {$this->record->status}")
            ->line("Iniciado: {$this->record->started_at?->format('d/m/Y')}")
            ->when($this->record->technician, fn ($msg) => $msg->line("Técnico: {$this->record->technician}"))
            ->action('Ver mantenimiento', url("/admin/maintenance-records/{$this->record->id}"))
            ->salutation('ITAssets');
    }

    public function toArray(object $notifiable): array
    {
        $assetTag = $this->record->asset?->asset_tag ?? 'N/A';
        $assetName = $this->record->asset?->name ?? 'N/A';

        return [
            'maintenance_id' => $this->record->id,
            'asset_tag'      => $assetTag,
            'asset_name'     => $assetName,
            'type'           => $this->record->type,
            'status'         => $this->record->status,
            'started_at'     => $this->record->started_at?->format('d/m/Y'),
            'alert_type'     => $this->alertType,
            'message'        => match ($this->alertType) {
                'prolonged' => "Mantenimiento prolongado: {$assetTag} - {$assetName} ({$this->record->started_at?->diffForHumans()})",
                'started'   => "Mantenimiento iniciado: {$assetTag} - {$assetName}",
                default     => "Alerta de mantenimiento: {$assetTag} - {$assetName}",
            },
        ];
    }

    public function toFilament(): FilamentNotification
    {
        $assetTag = $this->record->asset?->asset_tag ?? 'N/A';
        $assetName = $this->record->asset?->name ?? 'N/A';

        $title = match ($this->alertType) {
            'prolonged' => "Mantenimiento prolongado: {$assetTag} {$assetName}",
            'started'   => "Mantenimiento iniciado: {$assetTag} {$assetName}",
            default     => "Alerta de mantenimiento: {$assetTag} {$assetName}",
        };

        return FilamentNotification::make()
            ->title($title)
            ->body("Tipo: {$this->record->type} — Estado: {$this->record->status}")
            ->status($this->alertType === 'prolonged' ? 'warning' : 'info');
    }
}

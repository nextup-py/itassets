<?php

namespace App\Notifications;

use App\Models\Asset;
use App\Models\Employee;
use App\Notifications\Concerns\SendsToManagers;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssetAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable, SendsToManagers;

    public function __construct(
        public Asset $asset,
        public string $alertType,
        public ?Employee $employee = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $assetLabel = "{$this->asset->asset_tag} {$this->asset->name}";
        $employeeName = $this->employee?->name ?? 'N/A';

        $subject = match ($this->alertType) {
            'assigned' => "Activo asignado: {$assetLabel}",
            'returned' => "Activo devuelto: {$assetLabel}",
            default    => "Movimiento de activo: {$assetLabel}",
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hola, ' . $notifiable->name)
            ->line(match ($this->alertType) {
                'assigned' => "El activo **{$assetLabel}** fue asignado a **{$employeeName}**.",
                'returned' => "El activo **{$assetLabel}** fue devuelto por **{$employeeName}**.",
                default    => "Movimiento registrado para el activo **{$assetLabel}**.",
            })
            ->action('Ver activo', url("/admin/assets/{$this->asset->id}"))
            ->salutation('ITAssets');
    }

    public function toArray(object $notifiable): array
    {
        $assetLabel = "{$this->asset->asset_tag} {$this->asset->name}";
        $employeeName = $this->employee?->name ?? 'N/A';

        return [
            'asset_id'    => $this->asset->id,
            'asset_tag'   => $this->asset->asset_tag,
            'asset_name'  => $this->asset->name,
            'employee'    => $employeeName,
            'alert_type'  => $this->alertType,
            'message'     => match ($this->alertType) {
                'assigned' => "Activo asignado: {$assetLabel} → {$employeeName}",
                'returned' => "Activo devuelto: {$assetLabel} ({$employeeName})",
                default    => "Movimiento de activo: {$assetLabel}",
            },
        ];
    }

    public function toFilament(): FilamentNotification
    {
        $assetLabel = "{$this->asset->asset_tag} {$this->asset->name}";
        $employeeName = $this->employee?->name ?? 'N/A';

        $title = match ($this->alertType) {
            'assigned' => "Activo asignado: {$assetLabel}",
            'returned' => "Activo devuelto: {$assetLabel}",
            default    => "Movimiento de activo: {$assetLabel}",
        };

        return FilamentNotification::make()
            ->title($title)
            ->body("Empleado: {$employeeName}")
            ->status($this->alertType === 'assigned' ? 'success' : 'info');
    }
}

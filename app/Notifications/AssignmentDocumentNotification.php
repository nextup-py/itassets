<?php

namespace App\Notifications;

use App\Models\Assignment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentDocumentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Assignment $assignment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pdf = Pdf::loadView('pdf.assignment', [
            'assignment' => $this->assignment,
        ])->output();

        return (new MailMessage)
            ->subject('Documento de asignación de equipamiento')
            ->greeting('Hola, ' . $notifiable->name)
            ->line('Se te asignó equipamiento. Adjuntamos el documento de asignación correspondiente.')
            ->salutation('ITAssets')
            ->attachData($pdf, 'asignacion_' . $this->assignment->id . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}

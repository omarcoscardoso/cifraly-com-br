<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\EventRoster;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventRosterInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public EventRoster $roster
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->roster->event;
        $role = $this->roster->role;
        $org = $event?->organization;

        $confirmationUrl = route('roster.confirm', [
            'token' => $this->roster->confirmation_token,
        ]);

        $eventDate = $event?->starts_at?->format('d/m/Y \à\s H:i') ?? 'Data a confirmar';

        return (new MailMessage)
            ->subject("Escala: {$event?->title} - {$org?->name}")
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Você foi escalado para servir na função **{$role?->name}** no evento **{$event?->title}**.")
            ->line("Data do Evento: **{$eventDate}**")
            ->line("Organização: **{$org?->name}**")
            ->action('Responder Escala', $confirmationUrl)
            ->line('Por favor, confirme ou recuse sua presença para que a equipe possa se organizar.')
            ->salutation('Atenciosamente, Equipe '.$org?->name);
    }
}

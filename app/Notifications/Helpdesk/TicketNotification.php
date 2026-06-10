<?php

declare(strict_types=1);

namespace App\Notifications\Helpdesk;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public SupportTicket $ticket, public string $action) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $t = $this->ticket;

        return (new MailMessage)
            ->subject($this->subjectLine()." — {$t->ticket_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line($this->bodyLine())
            ->line("Subject: {$t->subject}")
            ->line("Priority: ".ucfirst($t->priority))
            ->line("Status: ".str($t->status)->headline())
            ->action('View Ticket', url("/helpdesk/{$t->id}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'helpdesk',
            'action'        => $this->action,
            'id'            => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject'       => $this->ticket->subject,
            'status'        => $this->ticket->status,
            'message'       => $this->bodyLine(),
        ];
    }

    protected function subjectLine(): string
    {
        return match ($this->action) {
            'created'  => 'New support ticket registered',
            'assigned' => 'Support ticket assigned',
            default    => 'Support ticket status updated',
        };
    }

    protected function bodyLine(): string
    {
        return match ($this->action) {
            'created'  => "A new support ticket ({$this->ticket->ticket_number}) has been registered.",
            'assigned' => "Support ticket {$this->ticket->ticket_number} has been assigned.",
            default    => "Support ticket {$this->ticket->ticket_number} is now ".str($this->ticket->status)->headline().'.',
        };
    }
}

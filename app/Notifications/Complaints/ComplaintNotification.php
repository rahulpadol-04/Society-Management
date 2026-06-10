<?php

declare(strict_types=1);

namespace App\Notifications\Complaints;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Complaint $complaint, public string $action) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $c = $this->complaint;

        return (new MailMessage)
            ->subject($this->subjectLine()." — {$c->reference}")
            ->greeting("Hello {$notifiable->name},")
            ->line($this->bodyLine())
            ->line("Title: {$c->title}")
            ->line("Priority: ".ucfirst($c->priority))
            ->line("Status: ".str($c->status)->headline())
            ->action('View Complaint', url("/complaints/{$c->id}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'complaint',
            'action'    => $this->action,
            'id'        => $this->complaint->id,
            'reference' => $this->complaint->reference,
            'title'     => $this->complaint->title,
            'status'    => $this->complaint->status,
            'message'   => $this->bodyLine(),
        ];
    }

    protected function subjectLine(): string
    {
        return match ($this->action) {
            'created'  => 'New complaint registered',
            'assigned' => 'Complaint assigned',
            default    => 'Complaint status updated',
        };
    }

    protected function bodyLine(): string
    {
        return match ($this->action) {
            'created'  => "A new complaint ({$this->complaint->reference}) has been registered.",
            'assigned' => "Complaint {$this->complaint->reference} has been assigned.",
            default    => "Complaint {$this->complaint->reference} is now ".str($this->complaint->status)->headline().'.',
        };
    }
}

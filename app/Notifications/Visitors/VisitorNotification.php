<?php

declare(strict_types=1);

namespace App\Notifications\Visitors;

use App\Models\VisitorLog;
use App\Models\VisitorPass;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VisitorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param  VisitorPass|VisitorLog  $subject */
    public function __construct(
        public readonly VisitorPass|VisitorLog $subject,
        public readonly string $action,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pass = $this->resolvePass();

        return (new MailMessage)
            ->subject($this->subjectLine().' — '.($pass?->code ?? 'Visitor'))
            ->greeting("Hello {$notifiable->name},")
            ->line($this->bodyLine())
            ->line('Visitor: '.($pass?->name ?? ($this->subject instanceof VisitorLog ? $this->subject->name : '—')))
            ->line('Type: '.ucfirst($pass?->type ?? ($this->subject instanceof VisitorLog ? $this->subject->type : '—')))
            ->action('View Visitor Passes', url('/visitors'));
    }

    public function toArray(object $notifiable): array
    {
        $pass = $this->resolvePass();

        return [
            'type'    => 'visitor',
            'action'  => $this->action,
            'pass_id' => $pass?->id,
            'code'    => $pass?->code,
            'message' => $this->bodyLine(),
        ];
    }

    protected function resolvePass(): ?VisitorPass
    {
        if ($this->subject instanceof VisitorPass) {
            return $this->subject;
        }

        return $this->subject->pass;
    }

    protected function subjectLine(): string
    {
        return match ($this->action) {
            'requested' => 'New visitor pass requested',
            'approved'  => 'Your visitor pass has been approved',
            'checked_in'=> 'Your visitor has arrived',
            default     => 'Visitor update',
        };
    }

    protected function bodyLine(): string
    {
        $pass = $this->resolvePass();
        $name = $pass?->name ?? ($this->subject instanceof VisitorLog ? $this->subject->name : 'A visitor');
        $code = $pass?->code ?? '';

        return match ($this->action) {
            'requested'  => "A visitor pass ({$code}) has been requested for {$name}.",
            'approved'   => "Your visitor pass ({$code}) for {$name} has been approved.",
            'checked_in' => "{$name} has checked in at the gate.",
            default      => "Visitor update for pass {$code}.",
        };
    }
}

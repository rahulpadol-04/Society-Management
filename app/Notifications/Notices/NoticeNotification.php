<?php

declare(strict_types=1);

namespace App\Notifications\Notices;

use App\Models\Notice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NoticeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Notice $notice) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $n = $this->notice;

        return (new MailMessage)
            ->subject($this->subjectLine())
            ->greeting("Hello {$notifiable->name},")
            ->line($this->bodyLine())
            ->line("Title: {$n->title}")
            ->line('Category: '.ucfirst($n->category))
            ->action('View Notice', url("/notices/{$n->id}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'notice',
            'id'       => $this->notice->id,
            'title'    => $this->notice->title,
            'category' => $this->notice->category,
            'message'  => $this->bodyLine(),
        ];
    }

    protected function subjectLine(): string
    {
        return match ($this->notice->category) {
            'announcement' => 'New Announcement',
            'circular'     => 'New Circular',
            'event'        => 'Upcoming Event',
            default        => 'New Notice',
        };
    }

    protected function bodyLine(): string
    {
        return "A new ".ucfirst($this->notice->category)." has been published: {$this->notice->title}.";
    }
}

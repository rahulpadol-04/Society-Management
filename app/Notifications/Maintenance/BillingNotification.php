<?php

declare(strict_types=1);

namespace App\Notifications\Maintenance;

use App\Models\MaintenanceBill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BillingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public MaintenanceBill $bill,
        public string          $action,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $b       = $this->bill;
        $dueDate = $b->due_date ? $b->due_date->format('d M Y') : '';

        return (new MailMessage)
            ->subject($this->subjectLine().' — '.$b->bill_number)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->bodyLine())
            ->line('Bill Number: '.$b->bill_number)
            ->line('Period: '.$b->period)
            ->line('Total: '.money($b->total))
            ->line('Due Date: '.$dueDate)
            ->action('View Bill', url('/maintenance/bills/'.$b->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'maintenance_billing',
            'action'      => $this->action,
            'id'          => $this->bill->id,
            'bill_number' => $this->bill->bill_number,
            'period'      => $this->bill->period,
            'total'       => $this->bill->total,
            'status'      => $this->bill->status,
            'message'     => $this->bodyLine(),
        ];
    }

    protected function subjectLine(): string
    {
        return match ($this->action) {
            'generated' => 'Maintenance bill generated',
            'payment'   => 'Payment received',
            'due_soon'  => 'Maintenance bill due soon',
            'overdue'   => 'Maintenance bill overdue',
            default     => 'Maintenance billing update',
        };
    }

    protected function bodyLine(): string
    {
        $bn      = $this->bill->bill_number;
        $period  = $this->bill->period;
        $dueDate = $this->bill->due_date ? $this->bill->due_date->format('d M Y') : '';
        $balance = money($this->bill->balance);

        return match ($this->action) {
            'generated' => 'Your maintenance bill '.$bn.' for period '.$period.' has been generated.',
            'payment'   => 'Payment received for bill '.$bn.'. Balance: '.$balance.'.',
            'due_soon'  => 'Your maintenance bill '.$bn.' is due on '.$dueDate.'.',
            'overdue'   => 'Your maintenance bill '.$bn.' is overdue. Please pay immediately.',
            default     => 'Maintenance billing update for '.$bn.'.',
        };
    }
}

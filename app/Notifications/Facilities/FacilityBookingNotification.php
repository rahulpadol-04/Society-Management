<?php

declare(strict_types=1);

namespace App\Notifications\Facilities;

use App\Models\FacilityBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FacilityBookingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public FacilityBooking $booking, public string $action) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $b = $this->booking;

        return (new MailMessage)
            ->subject($this->subjectLine())
            ->greeting("Hello {$notifiable->name},")
            ->line($this->bodyLine())
            ->line("Facility: {$b->facility?->name}")
            ->line("Date: {$b->booking_date?->format('d M Y')}")
            ->line("Time: {$b->start_time} — {$b->end_time}")
            ->line("Status: ".str($b->status)->headline())
            ->action('View Booking', url("/bookings/{$b->id}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'facility_booking',
            'action'   => $this->action,
            'id'       => $this->booking->id,
            'facility' => $this->booking->facility?->name,
            'date'     => $this->booking->booking_date?->format('Y-m-d'),
            'status'   => $this->booking->status,
            'message'  => $this->bodyLine(),
        ];
    }

    protected function subjectLine(): string
    {
        return match ($this->action) {
            'booked'   => 'New facility booking request',
            'approved' => 'Facility booking approved',
            'rejected' => 'Facility booking rejected',
            'cancelled' => 'Facility booking cancelled',
            default    => 'Facility booking update',
        };
    }

    protected function bodyLine(): string
    {
        $facility = $this->booking->facility?->name ?? 'the facility';

        return match ($this->action) {
            'booked'   => "A new booking request has been made for {$facility}.",
            'approved' => "Your booking for {$facility} has been approved.",
            'rejected' => "Your booking for {$facility} has been rejected.",
            'cancelled' => "A booking for {$facility} has been cancelled.",
            default    => "Your booking for {$facility} has been updated to ".str($this->booking->status)->headline().'.',
        };
    }
}

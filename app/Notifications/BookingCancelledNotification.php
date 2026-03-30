<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Booking cancelled')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your booking has been cancelled.')
            ->line('Service: '.$this->booking->service->name)
            ->line('Time: '.$this->booking->starts_at->format('d.m.Y H:i').' - '.$this->booking->ends_at->format('H:i'))
            ->line('Status: Cancelled booking')
            ->action('View bookings', route('dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'type' => 'cancelled',
        ];
    }
}

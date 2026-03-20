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
            ->subject('Otkazuvanje na rezervacija')
            ->greeting('Zdravo '.$notifiable->name.',')
            ->line('Rezervacijata e otkazana.')
            ->line('Usluga: '.$this->booking->service->name)
            ->line('Termin: '.$this->booking->starts_at->format('d.m.Y H:i').' - '.$this->booking->ends_at->format('H:i'))
            ->line('Status: Otkazana rezervacija')
            ->action('Pregled na rezervacii', route('dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'type' => 'cancelled',
        ];
    }
}

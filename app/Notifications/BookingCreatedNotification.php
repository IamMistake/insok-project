<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Booking $booking) {}

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
        return (new MailMessage)
            ->subject('Booking confirmation')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your booking has been created successfully.')
            ->line('Service: '.$this->booking->service->name)
            ->line('Time: '.$this->booking->starts_at->format('d.m.Y H:i').' - '.$this->booking->ends_at->format('H:i'))
            ->line('Status: Active booking')
            ->action('View bookings', route('dashboard'))
            ->line('Thank you for using the app.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'type' => 'created',
        ];
    }
}

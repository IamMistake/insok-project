<?php

namespace App\Notifications;

use App\Models\Booking;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingRescheduledNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Booking $booking,
        private readonly CarbonImmutable $oldStartsAt,
        private readonly CarbonImmutable $oldEndsAt,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Prezakazuvanje na rezervacija')
            ->greeting('Zdravo '.$notifiable->name.',')
            ->line('Rezervacijata e uspesno prezakazana.')
            ->line('Usluga: '.$this->booking->service->name)
            ->line('Prethoden termin: '.$this->oldStartsAt->format('d.m.Y H:i').' - '.$this->oldEndsAt->format('H:i'))
            ->line('Nov termin: '.$this->booking->starts_at->format('d.m.Y H:i').' - '.$this->booking->ends_at->format('H:i'))
            ->action('Pregled na rezervacii', route('dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'type' => 'rescheduled',
        ];
    }
}

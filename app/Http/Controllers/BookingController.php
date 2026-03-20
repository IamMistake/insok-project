<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\BookingCreatedNotification;
use App\Notifications\BookingRescheduledNotification;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function availability(Request $request, AvailabilityService $availabilityService): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'date' => ['required', 'date'],
            'ignore_booking_id' => ['nullable', 'integer', 'exists:bookings,id'],
        ]);

        $service = Service::query()->active()->findOrFail($validated['service_id']);
        $date = CarbonImmutable::parse($validated['date']);
        $ignoreBookingId = $validated['ignore_booking_id'] ?? null;

        if ($ignoreBookingId !== null) {
            $ignoreBooking = Booking::query()->findOrFail($ignoreBookingId);
            abort_unless($ignoreBooking->user_id === Auth::id(), 403);
        }

        return response()->json([
            'slots' => $availabilityService->availableSlots($service, $date, $ignoreBookingId),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function store(StoreBookingRequest $request, AvailabilityService $availabilityService): RedirectResponse
    {
        $service = Service::query()->active()->findOrFail($request->integer('service_id'));
        $startsAt = CarbonImmutable::parse($request->input('starts_at'));

        if (! $availabilityService->canBook($service, $startsAt)) {
            throw ValidationException::withMessages([
                'starts_at' => 'Izbraniot termin poveke ne e sloboden. Izberete drug termin.',
            ]);
        }

        $booking = DB::transaction(function () use ($request, $service, $startsAt, $availabilityService): Booking {
            if (! $availabilityService->canBook($service, $startsAt)) {
                throw ValidationException::withMessages([
                    'starts_at' => 'Izbraniot termin poveke ne e sloboden. Izberete drug termin.',
                ]);
            }

            return Booking::query()->create([
                'user_id' => Auth::id(),
                'service_id' => $service->id,
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->addMinutes($service->duration_minutes),
                'status' => Booking::STATUS_BOOKED,
                'notes' => $request->input('notes'),
            ]);
        });

        $booking->load(['service', 'user']);
        $this->notifyBookingParticipants($booking, new BookingCreatedNotification($booking));

        return redirect()
            ->route('calendar.index')
            ->with('status', 'Rezervacijata e uspesno kreirana.');
    }

    public function reschedule(Request $request, Booking $booking, AvailabilityService $availabilityService): RedirectResponse
    {
        abort_unless($booking->user_id === Auth::id(), 403);

        if ($booking->status !== Booking::STATUS_BOOKED) {
            return back()->withErrors([
                'booking' => 'Mozat da se prezakazat samo aktivni rezervacii.',
            ]);
        }

        if ($booking->starts_at->isPast()) {
            return back()->withErrors([
                'booking' => 'Pominat termin ne moze da se prezakaze.',
            ]);
        }

        $validated = $request->validate([
            'starts_at' => ['required', 'date'],
        ]);

        $startsAt = CarbonImmutable::parse($validated['starts_at']);
        $service = $booking->service;
        $originalStartsAt = $booking->starts_at->toImmutable();
        $originalEndsAt = $booking->ends_at->toImmutable();

        if ($startsAt->equalTo($originalStartsAt)) {
            return back()->with('status', 'Rezervacijata vekje e na izbraniot termin.');
        }

        if (! $availabilityService->canBook($service, $startsAt, $booking->id)) {
            return back()->withErrors([
                'booking' => 'Noviot termin ne e sloboden. Izberete drug.',
            ]);
        }

        DB::transaction(function () use ($booking, $service, $startsAt, $availabilityService): void {
            if (! $availabilityService->canBook($service, $startsAt, $booking->id)) {
                throw ValidationException::withMessages([
                    'booking' => 'Noviot termin vo megjuvreme stana nedostapen.',
                ]);
            }

            $booking->update([
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->addMinutes($service->duration_minutes),
            ]);
        });

        $booking->refresh()->load(['service', 'user']);
        $this->notifyBookingParticipants($booking, new BookingRescheduledNotification($booking, $originalStartsAt, $originalEndsAt));

        return redirect()
            ->route('calendar.index')
            ->with('status', 'Rezervacijata e uspesno prezakazana.');
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === Auth::id(), 403);

        if ($booking->status === Booking::STATUS_CANCELLED) {
            return back()->with('status', 'Rezervacijata vekje e otkazana.');
        }

        if ($booking->starts_at->isPast()) {
            return back()->withErrors([
                'booking' => 'Ne mozete da otkazete pominat termin.',
            ]);
        }

        $booking->update([
            'status' => Booking::STATUS_CANCELLED,
        ]);

        $booking->load(['service', 'user']);
        $this->notifyBookingParticipants($booking, new BookingCancelledNotification($booking));

        return redirect()
            ->route('calendar.index')
            ->with('status', 'Rezervacijata e otkazana.');
    }

    private function notifyBookingParticipants(Booking $booking, object $notification): void
    {
        $booking->user->notify($notification);

        $adminNotificationEmail = env('ADMIN_NOTIFICATION_EMAIL');

        if ($adminNotificationEmail) {
            Notification::route('mail', $adminNotificationEmail)->notify($notification);

            return;
        }

        User::query()
            ->where('role', User::ROLE_ADMIN)
            ->get()
            ->each(fn (User $admin) => $admin->notify($notification));
    }
}

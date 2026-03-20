<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Service;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function availability(Request $request, AvailabilityService $availabilityService): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'date' => ['required', 'date'],
        ]);

        $service = Service::query()->active()->findOrFail($validated['service_id']);
        $date = CarbonImmutable::parse($validated['date']);

        return response()->json([
            'slots' => $availabilityService->availableSlots($service, $date),
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

        DB::transaction(function () use ($request, $service, $startsAt): void {
            $endsAt = $startsAt->addMinutes($service->duration_minutes);

            Booking::query()->create([
                'user_id' => Auth::id(),
                'service_id' => $service->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => Booking::STATUS_BOOKED,
                'notes' => $request->input('notes'),
            ]);
        });

        return redirect()
            ->route('calendar.index')
            ->with('status', 'Rezervacijata e uspesno kreirana.');
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

        return redirect()
            ->route('calendar.index')
            ->with('status', 'Rezervacijata e otkazana.');
    }
}

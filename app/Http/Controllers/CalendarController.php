<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(): View
    {
        return view('calendar.index', [
            'services' => Service::query()->active()->orderBy('name')->get(),
            'upcomingBookings' => Booking::query()
                ->with('service:id,name')
                ->where('user_id', Auth::id())
                ->where('starts_at', '>=', now()->subDay())
                ->orderBy('starts_at')
                ->get(),
        ]);
    }

    public function events(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);

        $start = \Carbon\CarbonImmutable::parse($validated['start'], config('app.timezone'));
        $end = \Carbon\CarbonImmutable::parse($validated['end'], config('app.timezone'));

        $events = Booking::query()
            ->with('service:id,name')
            ->where('user_id', Auth::id())
            ->where('starts_at', '<', $end->toDateTimeString())
            ->where('ends_at', '>', $start->toDateTimeString())
            ->get()
            ->map(function (Booking $booking): array {
                return [
                    'id' => $booking->id,
                    'title' => $booking->service?->name ?? 'Booking',
                    'start' => $booking->starts_at?->toIso8601String(),
                    'end' => $booking->ends_at?->toIso8601String(),
                    'color' => $booking->status === Booking::STATUS_BOOKED ? '#2563eb' : '#6b7280',
                ];
            });

        return response()->json($events);
    }
}

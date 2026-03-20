<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedPeriod;
use App\Models\Booking;
use App\Models\RecurringBlockedPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(): View
    {
        return view('admin.calendar.index');
    }

    public function events(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);

        $bookings = Booking::query()
            ->with(['service:id,name', 'user:id,name'])
            ->where('starts_at', '<', $validated['end'])
            ->where('ends_at', '>', $validated['start'])
            ->get()
            ->map(function (Booking $booking): array {
                return [
                    'id' => 'booking-'.$booking->id,
                    'title' => sprintf(
                        '%s - %s',
                        $booking->service?->name ?? 'Usluga',
                        $booking->user?->name ?? 'Klient',
                    ),
                    'start' => $booking->starts_at?->toIso8601String(),
                    'end' => $booking->ends_at?->toIso8601String(),
                    'color' => $booking->status === Booking::STATUS_BOOKED ? '#0f766e' : '#6b7280',
                ];
            });

        $blockedPeriods = BlockedPeriod::query()
            ->where('starts_at', '<', $validated['end'])
            ->where('ends_at', '>', $validated['start'])
            ->get()
            ->map(function (BlockedPeriod $blockedPeriod): array {
                return [
                    'id' => 'blocked-'.$blockedPeriod->id,
                    'title' => $blockedPeriod->reason ?: 'Blokiran termin',
                    'start' => $blockedPeriod->starts_at?->toIso8601String(),
                    'end' => $blockedPeriod->ends_at?->toIso8601String(),
                    'color' => '#dc2626',
                ];
            });

        $rangeStart = CarbonImmutable::parse($validated['start'])->startOfDay();
        $rangeEnd = CarbonImmutable::parse($validated['end'])->endOfDay();
        $recurringBlockedPeriods = collect();

        for ($cursor = $rangeStart; $cursor->lte($rangeEnd); $cursor = $cursor->addDay()) {
            $dailyEvents = RecurringBlockedPeriod::query()
                ->active()
                ->forDate($cursor)
                ->get()
                ->map(function (RecurringBlockedPeriod $blockedPeriod) use ($cursor): array {
                    return [
                        'id' => 'recurring-blocked-'.$blockedPeriod->id.'-'.$cursor->format('Ymd'),
                        'title' => $blockedPeriod->reason ?: 'Povtorliva blokada',
                        'start' => $cursor->format('Y-m-d').'T'.substr($blockedPeriod->start_time, 0, 8),
                        'end' => $cursor->format('Y-m-d').'T'.substr($blockedPeriod->end_time, 0, 8),
                        'color' => '#f97316',
                    ];
                });

            $recurringBlockedPeriods = $recurringBlockedPeriods->concat($dailyEvents);
        }

        return response()->json($bookings->concat($blockedPeriods)->concat($recurringBlockedPeriods)->values());
    }
}

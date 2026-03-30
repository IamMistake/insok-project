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

        $start = CarbonImmutable::parse($validated['start'], config('app.timezone'));
        $end = CarbonImmutable::parse($validated['end'], config('app.timezone'));

        $bookings = Booking::query()
            ->with(['service:id,name,description,duration_minutes', 'user:id,name'])
            ->where('starts_at', '<', $end->toDateTimeString())
            ->where('ends_at', '>', $start->toDateTimeString())
            ->get()
            ->map(function (Booking $booking): array {
                $statusLabel = $booking->status === Booking::STATUS_BOOKED ? 'Active' : 'Cancelled';

                return [
                    'id' => 'booking-'.$booking->id,
                    'title' => sprintf(
                        '%s - %s',
                        $booking->service?->name ?? 'Service',
                        $booking->user?->name ?? 'Client',
                    ),
                    'start' => $booking->starts_at?->toIso8601String(),
                    'end' => $booking->ends_at?->toIso8601String(),
                    'color' => $booking->status === Booking::STATUS_BOOKED ? '#0f766e' : '#6b7280',
                    'extendedProps' => [
                        'type' => 'booking',
                        'service_name' => $booking->service?->name,
                        'service_description' => $booking->service?->description,
                        'duration_minutes' => $booking->service?->duration_minutes,
                        'notes' => $booking->notes,
                        'status' => $booking->status,
                        'status_label' => $statusLabel,
                        'client_name' => $booking->user?->name,
                    ],
                ];
            });

        $blockedPeriods = BlockedPeriod::query()
            ->where('starts_at', '<', $end->toDateTimeString())
            ->where('ends_at', '>', $start->toDateTimeString())
            ->get()
            ->map(function (BlockedPeriod $blockedPeriod): array {
                return [
                    'id' => 'blocked-'.$blockedPeriod->id,
                    'title' => $blockedPeriod->reason ?: 'Blocked period',
                    'start' => $blockedPeriod->starts_at?->toIso8601String(),
                    'end' => $blockedPeriod->ends_at?->toIso8601String(),
                    'color' => '#dc2626',
                    'extendedProps' => [
                        'type' => 'blocked',
                        'reason' => $blockedPeriod->reason,
                    ],
                ];
            });

        $rangeStart = $start->startOfDay();
        $rangeEnd = $end->endOfDay();
        $recurringBlockedPeriods = collect();

        for ($cursor = $rangeStart; $cursor->lte($rangeEnd); $cursor = $cursor->addDay()) {
            $dailyEvents = RecurringBlockedPeriod::query()
                ->active()
                ->forDate($cursor)
                ->get()
                ->map(function (RecurringBlockedPeriod $blockedPeriod) use ($cursor): array {
                    return [
                        'id' => 'recurring-blocked-'.$blockedPeriod->id.'-'.$cursor->format('Ymd'),
                        'title' => $blockedPeriod->reason ?: 'Recurring block',
                        'start' => $cursor->format('Y-m-d').'T'.substr($blockedPeriod->start_time, 0, 8),
                        'end' => $cursor->format('Y-m-d').'T'.substr($blockedPeriod->end_time, 0, 8),
                        'color' => '#f97316',
                        'extendedProps' => [
                            'type' => 'recurring_blocked',
                            'reason' => $blockedPeriod->reason,
                        ],
                    ];
                });

            $recurringBlockedPeriods = $recurringBlockedPeriods->concat($dailyEvents);
        }

        return response()->json($bookings->concat($blockedPeriods)->concat($recurringBlockedPeriods)->values());
    }
}

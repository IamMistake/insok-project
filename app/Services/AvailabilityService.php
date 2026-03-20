<?php

namespace App\Services;

use App\Models\BlockedPeriod;
use App\Models\Booking;
use App\Models\BusinessHour;
use App\Models\RecurringBlockedPeriod;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class AvailabilityService
{
    private const SLOT_STEP_MINUTES = 30;

    /**
     * @return array<int, array{start: string, end: string, label: string}>
     */
    public function availableSlots(Service $service, CarbonImmutable $date, ?int $ignoreBookingId = null): array
    {
        $window = $this->workingWindow($date);

        if ($window === null) {
            return [];
        }

        [$dayStart, $dayEnd] = $window;
        $latestStart = $dayEnd->subMinutes($service->duration_minutes);

        if ($latestStart->lt($dayStart)) {
            return [];
        }

        $bookedRanges = $this->bookedRanges($dayStart, $dayEnd, $ignoreBookingId);
        $blockedRanges = $this->blockedRanges($date, $dayStart, $dayEnd);

        $now = CarbonImmutable::now();
        $cursor = $dayStart;
        $slots = [];

        while ($cursor->lte($latestStart)) {
            $slotEnd = $cursor->addMinutes($service->duration_minutes);

            if ($cursor->gte($now) && ! $this->overlaps($cursor, $slotEnd, $bookedRanges) && ! $this->overlaps($cursor, $slotEnd, $blockedRanges)) {
                $slots[] = [
                    'start' => $cursor->toIso8601String(),
                    'end' => $slotEnd->toIso8601String(),
                    'label' => $cursor->format('H:i'),
                ];
            }

            $cursor = $cursor->addMinutes(self::SLOT_STEP_MINUTES);
        }

        return $slots;
    }

    public function canBook(Service $service, CarbonImmutable $startsAt, ?int $ignoreBookingId = null): bool
    {
        if ($startsAt->lt(CarbonImmutable::now())) {
            return false;
        }

        if (! $service->is_active) {
            return false;
        }

        if ($startsAt->second !== 0) {
            return false;
        }

        $window = $this->workingWindow($startsAt);

        if ($window === null) {
            return false;
        }

        [$dayStart, $dayEnd] = $window;
        $endsAt = $startsAt->addMinutes($service->duration_minutes);

        if ($startsAt->lt($dayStart) || $endsAt->gt($dayEnd)) {
            return false;
        }

        if (! $this->isSlotAligned($startsAt)) {
            return false;
        }

        $hasBookingConflict = Booking::query()
            ->active()
            ->when($ignoreBookingId !== null, fn ($query) => $query->whereKeyNot($ignoreBookingId))
            ->overlapping($startsAt, $endsAt)
            ->exists();

        if ($hasBookingConflict) {
            return false;
        }

        $hasBlockedConflict = $this->hasBlockedConflict($startsAt, $endsAt);

        return ! $hasBlockedConflict;
    }

    public function isSlotAligned(CarbonImmutable $startsAt): bool
    {
        return $startsAt->minute % self::SLOT_STEP_MINUTES === 0 && $startsAt->second === 0;
    }

    /**
     * @return array{CarbonImmutable, CarbonImmutable}|null
     */
    private function workingWindow(CarbonImmutable $date): ?array
    {
        $businessHour = BusinessHour::query()
            ->where('weekday', $date->dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (! $businessHour || ! $businessHour->start_time || ! $businessHour->end_time) {
            return null;
        }

        $day = $date->format('Y-m-d');
        $dayStart = CarbonImmutable::parse("{$day} {$businessHour->start_time}");
        $dayEnd = CarbonImmutable::parse("{$day} {$businessHour->end_time}");

        if ($dayEnd->lte($dayStart)) {
            return null;
        }

        return [$dayStart, $dayEnd];
    }

    private function overlaps(CarbonImmutable $start, CarbonImmutable $end, Collection $ranges): bool
    {
        return $ranges->contains(function (object $range) use ($start, $end): bool {
            $rangeStart = CarbonImmutable::parse($range->starts_at);
            $rangeEnd = CarbonImmutable::parse($range->ends_at);

            return $rangeStart->lt($end) && $rangeEnd->gt($start);
        });
    }

    private function bookedRanges(CarbonImmutable $dayStart, CarbonImmutable $dayEnd, ?int $ignoreBookingId = null): Collection
    {
        return Booking::query()
            ->active()
            ->when($ignoreBookingId !== null, fn ($query) => $query->whereKeyNot($ignoreBookingId))
            ->overlapping($dayStart, $dayEnd)
            ->get(['starts_at', 'ends_at']);
    }

    private function blockedRanges(CarbonImmutable $date, CarbonImmutable $dayStart, CarbonImmutable $dayEnd): Collection
    {
        $oneOff = BlockedPeriod::query()
            ->overlapping($dayStart, $dayEnd)
            ->get(['starts_at', 'ends_at']);

        $recurring = RecurringBlockedPeriod::query()
            ->active()
            ->forDate($date)
            ->get()
            ->map(function (RecurringBlockedPeriod $blockedPeriod) use ($date): object {
                return (object) [
                    'starts_at' => $date->format('Y-m-d').' '.$blockedPeriod->start_time,
                    'ends_at' => $date->format('Y-m-d').' '.$blockedPeriod->end_time,
                ];
            });

        return $oneOff->concat($recurring)->values();
    }

    private function hasBlockedConflict(CarbonImmutable $startsAt, CarbonImmutable $endsAt): bool
    {
        $oneOffBlocked = BlockedPeriod::query()
            ->overlapping($startsAt, $endsAt)
            ->exists();

        if ($oneOffBlocked) {
            return true;
        }

        $recurringBlockedPeriods = RecurringBlockedPeriod::query()
            ->active()
            ->forDate($startsAt)
            ->get();

        return $recurringBlockedPeriods->contains(function (RecurringBlockedPeriod $blockedPeriod) use ($startsAt, $endsAt): bool {
            $rangeStart = CarbonImmutable::parse($startsAt->format('Y-m-d').' '.$blockedPeriod->start_time);
            $rangeEnd = CarbonImmutable::parse($startsAt->format('Y-m-d').' '.$blockedPeriod->end_time);

            return $rangeStart->lt($endsAt) && $rangeEnd->gt($startsAt);
        });
    }
}

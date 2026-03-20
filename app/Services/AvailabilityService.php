<?php

namespace App\Services;

use App\Models\BlockedPeriod;
use App\Models\Booking;
use App\Models\BusinessHour;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class AvailabilityService
{
    private const SLOT_STEP_MINUTES = 30;

    /**
     * @return array<int, array{start: string, end: string, label: string}>
     */
    public function availableSlots(Service $service, CarbonImmutable $date): array
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

        $bookedRanges = Booking::query()
            ->active()
            ->where('starts_at', '<', $dayEnd)
            ->where('ends_at', '>', $dayStart)
            ->get(['starts_at', 'ends_at']);

        $blockedRanges = BlockedPeriod::query()
            ->where('starts_at', '<', $dayEnd)
            ->where('ends_at', '>', $dayStart)
            ->get(['starts_at', 'ends_at']);

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

    public function canBook(Service $service, CarbonImmutable $startsAt): bool
    {
        if ($startsAt->lt(CarbonImmutable::now())) {
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

        $hasBookingConflict = Booking::query()
            ->active()
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();

        if ($hasBookingConflict) {
            return false;
        }

        $hasBlockedConflict = BlockedPeriod::query()
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();

        return ! $hasBlockedConflict;
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
}

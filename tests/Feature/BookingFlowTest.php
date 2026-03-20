<?php

namespace Tests\Feature;

use App\Models\BlockedPeriod;
use App\Models\Booking;
use App\Models\BusinessHour;
use App\Models\RecurringBlockedPeriod;
use App\Models\Service;
use App\Models\User;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\BookingCreatedNotification;
use App\Notifications\BookingRescheduledNotification;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_create_booking_for_available_slot_and_notifications_are_sent(): void
    {
        Notification::fake();
        $this->seedBusinessHours();

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $service = $this->createService(30);
        $startsAt = $this->futureWeekdayAt(10, 0);

        $response = $this->actingAs($client)->post(route('bookings.store'), [
            'service_id' => $service->id,
            'starts_at' => $startsAt->toIso8601String(),
        ]);

        $response->assertRedirect(route('calendar.index'));

        $booking = Booking::query()->firstOrFail();
        $this->assertSame($startsAt->toDateTimeString(), $booking->starts_at->toDateTimeString());
        Notification::assertSentTo($client, BookingCreatedNotification::class);
        Notification::assertSentTo($admin, BookingCreatedNotification::class);
    }

    public function test_overlapping_slot_cannot_be_booked(): void
    {
        $this->seedBusinessHours();

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = $this->createService(60);
        $startsAt = $this->futureWeekdayAt(11, 0);

        Booking::query()->create([
            'user_id' => $client->id,
            'service_id' => $service->id,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addMinutes(60),
            'status' => Booking::STATUS_BOOKED,
        ]);

        $response = $this->actingAs($client)->from(route('calendar.index'))->post(route('bookings.store'), [
            'service_id' => $service->id,
            'starts_at' => $startsAt->toIso8601String(),
        ]);

        $response
            ->assertRedirect(route('calendar.index'))
            ->assertSessionHasErrors('starts_at');

        $this->assertSame(1, Booking::query()->count());
    }

    public function test_adjacent_slot_is_allowed_when_previous_booking_ends_exactly_at_start(): void
    {
        $this->seedBusinessHours();

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = $this->createService(60);
        $firstStart = $this->futureWeekdayAt(9, 0);

        Booking::query()->create([
            'user_id' => $client->id,
            'service_id' => $service->id,
            'starts_at' => $firstStart,
            'ends_at' => $firstStart->addMinutes(60),
            'status' => Booking::STATUS_BOOKED,
        ]);

        $response = $this->actingAs($client)->post(route('bookings.store'), [
            'service_id' => $service->id,
            'starts_at' => $firstStart->addMinutes(60)->toIso8601String(),
        ]);

        $response->assertRedirect(route('calendar.index'));
        $this->assertSame(2, Booking::query()->count());
    }

    public function test_booking_outside_business_hours_is_rejected(): void
    {
        $this->seedBusinessHours();

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = $this->createService(60);
        $startsAt = $this->futureWeekdayAt(16, 30);

        $response = $this->actingAs($client)->from(route('calendar.index'))->post(route('bookings.store'), [
            'service_id' => $service->id,
            'starts_at' => $startsAt->toIso8601String(),
        ]);

        $response->assertRedirect(route('calendar.index'))->assertSessionHasErrors('starts_at');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_booking_in_recurring_blocked_period_is_rejected(): void
    {
        $this->seedBusinessHours();

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = $this->createService(30);
        $startsAt = $this->futureWeekdayAt(12, 0);

        RecurringBlockedPeriod::query()->create([
            'weekday' => $startsAt->dayOfWeek,
            'start_time' => '12:00',
            'end_time' => '13:00',
            'reason' => 'Dnevna pauza',
            'is_active' => true,
        ]);

        $response = $this->actingAs($client)->from(route('calendar.index'))->post(route('bookings.store'), [
            'service_id' => $service->id,
            'starts_at' => $startsAt->toIso8601String(),
        ]);

        $response->assertRedirect(route('calendar.index'))->assertSessionHasErrors('starts_at');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_booking_in_one_off_blocked_period_is_rejected(): void
    {
        $this->seedBusinessHours();

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = $this->createService(30);
        $startsAt = $this->futureWeekdayAt(14, 0);

        BlockedPeriod::query()->create([
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addHour(),
            'reason' => 'Sostanok',
        ]);

        $response = $this->actingAs($client)->from(route('calendar.index'))->post(route('bookings.store'), [
            'service_id' => $service->id,
            'starts_at' => $startsAt->toIso8601String(),
        ]);

        $response->assertRedirect(route('calendar.index'))->assertSessionHasErrors('starts_at');
    }

    public function test_client_can_reschedule_booking_safely_and_notifications_are_sent(): void
    {
        Notification::fake();
        $this->seedBusinessHours();

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $service = $this->createService(30);
        $booking = Booking::query()->create([
            'user_id' => $client->id,
            'service_id' => $service->id,
            'starts_at' => $this->futureWeekdayAt(10, 0),
            'ends_at' => $this->futureWeekdayAt(10, 30),
            'status' => Booking::STATUS_BOOKED,
        ]);

        $newStartsAt = $this->futureWeekdayAt(11, 0);

        $response = $this->actingAs($client)->patch(route('bookings.reschedule', $booking), [
            'starts_at' => $newStartsAt->toIso8601String(),
        ]);

        $response->assertRedirect(route('calendar.index'));
        $this->assertSame($newStartsAt->toDateTimeString(), $booking->fresh()->starts_at->toDateTimeString());
        Notification::assertSentTo($client, BookingRescheduledNotification::class);
        Notification::assertSentTo($admin, BookingRescheduledNotification::class);
    }

    public function test_booking_cannot_be_rescheduled_into_conflicting_slot(): void
    {
        $this->seedBusinessHours();

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = $this->createService(30);
        $booking = Booking::query()->create([
            'user_id' => $client->id,
            'service_id' => $service->id,
            'starts_at' => $this->futureWeekdayAt(10, 0),
            'ends_at' => $this->futureWeekdayAt(10, 30),
            'status' => Booking::STATUS_BOOKED,
        ]);

        Booking::query()->create([
            'user_id' => $client->id,
            'service_id' => $service->id,
            'starts_at' => $this->futureWeekdayAt(11, 0),
            'ends_at' => $this->futureWeekdayAt(11, 30),
            'status' => Booking::STATUS_BOOKED,
        ]);

        $response = $this->actingAs($client)->from(route('calendar.index'))->patch(route('bookings.reschedule', $booking), [
            'starts_at' => $this->futureWeekdayAt(11, 0)->toIso8601String(),
        ]);

        $response->assertRedirect(route('calendar.index'))->assertSessionHasErrors('booking');
        $this->assertSame($this->futureWeekdayAt(10, 0)->toDateTimeString(), $booking->fresh()->starts_at->toDateTimeString());
    }

    public function test_client_can_cancel_booking_and_notifications_are_sent(): void
    {
        Notification::fake();
        $this->seedBusinessHours();

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $service = $this->createService(30);
        $booking = Booking::query()->create([
            'user_id' => $client->id,
            'service_id' => $service->id,
            'starts_at' => $this->futureWeekdayAt(10, 0),
            'ends_at' => $this->futureWeekdayAt(10, 30),
            'status' => Booking::STATUS_BOOKED,
        ]);

        $response = $this->actingAs($client)->delete(route('bookings.destroy', $booking));

        $response->assertRedirect(route('calendar.index'));
        $this->assertSame(Booking::STATUS_CANCELLED, $booking->fresh()->status);
        Notification::assertSentTo($client, BookingCancelledNotification::class);
        Notification::assertSentTo($admin, BookingCancelledNotification::class);
    }

    public function test_user_cannot_manage_someone_elses_booking(): void
    {
        $this->seedBusinessHours();

        $owner = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $otherClient = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = $this->createService(30);
        $booking = Booking::query()->create([
            'user_id' => $owner->id,
            'service_id' => $service->id,
            'starts_at' => $this->futureWeekdayAt(10, 0),
            'ends_at' => $this->futureWeekdayAt(10, 30),
            'status' => Booking::STATUS_BOOKED,
        ]);

        $this->actingAs($otherClient)
            ->patch(route('bookings.reschedule', $booking), ['starts_at' => $this->futureWeekdayAt(11, 0)->toIso8601String()])
            ->assertForbidden();

        $this->actingAs($otherClient)
            ->delete(route('bookings.destroy', $booking))
            ->assertForbidden();
    }

    public function test_admin_cannot_access_client_calendar_route(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('calendar.index'))
            ->assertForbidden();
    }

    private function seedBusinessHours(): void
    {
        foreach (range(0, 6) as $weekday) {
            BusinessHour::query()->create([
                'weekday' => $weekday,
                'is_active' => $weekday >= 1 && $weekday <= 5,
                'start_time' => $weekday >= 1 && $weekday <= 5 ? '09:00' : null,
                'end_time' => $weekday >= 1 && $weekday <= 5 ? '17:00' : null,
            ]);
        }
    }

    private function createService(int $durationMinutes): Service
    {
        return Service::query()->create([
            'name' => 'Test usluga '.$durationMinutes,
            'duration_minutes' => $durationMinutes,
            'price' => 500,
            'is_active' => true,
        ]);
    }

    private function futureWeekdayAt(int $hour, int $minute): CarbonImmutable
    {
        $date = CarbonImmutable::now()->addDay()->setTime($hour, $minute);

        while ($date->dayOfWeek === 0 || $date->dayOfWeek === 6) {
            $date = $date->addDay()->setTime($hour, $minute);
        }

        return $date;
    }
}

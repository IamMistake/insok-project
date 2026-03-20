<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BusinessHour;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_create_booking_for_available_slot(): void
    {
        $this->seedBusinessHours();

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = Service::query()->create([
            'name' => 'Konsultacija',
            'duration_minutes' => 30,
            'price' => 500,
            'is_active' => true,
        ]);

        $startsAt = CarbonImmutable::now()->addDays(2)->setTime(10, 0);

        $response = $this->actingAs($client)->post(route('bookings.store'), [
            'service_id' => $service->id,
            'starts_at' => $startsAt->toIso8601String(),
        ]);

        $response->assertRedirect(route('calendar.index'));

        $this->assertDatabaseHas('bookings', [
            'user_id' => $client->id,
            'service_id' => $service->id,
            'status' => Booking::STATUS_BOOKED,
        ]);
    }

    public function test_overlapping_slot_cannot_be_booked(): void
    {
        $this->seedBusinessHours();

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = Service::query()->create([
            'name' => 'Masaza',
            'duration_minutes' => 60,
            'price' => 1200,
            'is_active' => true,
        ]);

        $startsAt = CarbonImmutable::now()->addDays(2)->setTime(11, 0);

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
                'is_active' => true,
                'start_time' => '09:00',
                'end_time' => '17:00',
            ]);
        }
    }
}

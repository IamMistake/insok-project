<?php

namespace Tests\Feature;

use App\Models\BlockedPeriod;
use App\Models\Booking;
use App\Models\BusinessHour;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_availability_returns_slots_for_active_business_hours(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-18 08:00:00', 'Europe/Skopje'));

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = $this->createService(30);
        $date = CarbonImmutable::parse('2026-03-19', 'Europe/Skopje');

        BusinessHour::query()->create([
            'weekday' => $date->dayOfWeek,
            'is_active' => true,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $response = $this->actingAs($client)->getJson(route('bookings.availability', [
            'service_id' => $service->id,
            'date' => $date->toDateString(),
        ]));

        $response->assertOk();
        $this->assertNotEmpty($response->json('slots'));
    }

    public function test_availability_uses_admin_saved_business_hours(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-18 08:00:00', 'Europe/Skopje'));

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = $this->createService(30);
        $date = CarbonImmutable::parse('2026-03-19', 'Europe/Skopje');

        $payload = [
            'hours' => [
                $date->dayOfWeek => [
                    'is_active' => '1',
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                ],
            ],
        ];

        $this->actingAs($admin)->put(route('admin.business-hours.update'), $payload)
            ->assertRedirect(route('admin.business-hours.index'));

        $response = $this->actingAs($client)->getJson(route('bookings.availability', [
            'service_id' => $service->id,
            'date' => $date->toDateString(),
        ]));

        $response->assertOk();
        $this->assertNotEmpty($response->json('slots'));
    }

    public function test_availability_uses_app_timezone_for_slot_labels(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-21 08:00:00', 'Europe/Skopje'));

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = $this->createService(30);
        $date = CarbonImmutable::parse('2026-03-21', 'Europe/Skopje');

        BusinessHour::query()->create([
            'weekday' => $date->dayOfWeek,
            'is_active' => true,
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);

        $response = $this->actingAs($client)->getJson(route('bookings.availability', [
            'service_id' => $service->id,
            'date' => $date->toDateString(),
        ]));

        $response->assertOk();
        $response->assertJsonFragment(['label' => '08:00']);
    }

    public function test_availability_returns_empty_when_business_hours_inactive(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-18 08:00:00', 'Europe/Skopje'));

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = $this->createService(30);
        $date = CarbonImmutable::parse('2026-03-19', 'Europe/Skopje');

        BusinessHour::query()->create([
            'weekday' => $date->dayOfWeek,
            'is_active' => false,
            'start_time' => null,
            'end_time' => null,
        ]);

        $response = $this->actingAs($client)->getJson(route('bookings.availability', [
            'service_id' => $service->id,
            'date' => $date->toDateString(),
        ]));

        $response->assertOk();
        $response->assertExactJson([
            'slots' => [],
        ]);
    }

    public function test_availability_generates_15_minute_slots(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-18 07:00:00', 'Europe/Skopje'));

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = $this->createService(15);
        $date = CarbonImmutable::parse('2026-03-19', 'Europe/Skopje');

        BusinessHour::query()->create([
            'weekday' => $date->dayOfWeek,
            'is_active' => true,
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);

        $response = $this->actingAs($client)->getJson(route('bookings.availability', [
            'service_id' => $service->id,
            'date' => $date->toDateString(),
        ]));

        $response->assertOk();

        $labels = array_column($response->json('slots'), 'label');

        $this->assertSame(['08:00', '08:15', '08:30', '08:45'], $labels);
    }

    public function test_availability_generates_30_minute_slots(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-18 07:00:00', 'Europe/Skopje'));

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = $this->createService(30);
        $date = CarbonImmutable::parse('2026-03-19', 'Europe/Skopje');

        BusinessHour::query()->create([
            'weekday' => $date->dayOfWeek,
            'is_active' => true,
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);

        $response = $this->actingAs($client)->getJson(route('bookings.availability', [
            'service_id' => $service->id,
            'date' => $date->toDateString(),
        ]));

        $response->assertOk();

        $labels = array_column($response->json('slots'), 'label');

        $this->assertSame(['08:00', '08:30'], $labels);
    }

    public function test_availability_excludes_slot_that_exceeds_business_hours(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-18 07:00:00', 'Europe/Skopje'));

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = $this->createService(20);
        $date = CarbonImmutable::parse('2026-03-19', 'Europe/Skopje');

        BusinessHour::query()->create([
            'weekday' => $date->dayOfWeek,
            'is_active' => true,
            'start_time' => '08:00',
            'end_time' => '08:50',
        ]);

        $response = $this->actingAs($client)->getJson(route('bookings.availability', [
            'service_id' => $service->id,
            'date' => $date->toDateString(),
        ]));

        $response->assertOk();

        $labels = array_column($response->json('slots'), 'label');

        $this->assertSame(['08:00', '08:20'], $labels);
        $this->assertNotContains('08:40', $labels);
    }

    public function test_availability_excludes_slots_overlapping_existing_booking(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-18 07:00:00', 'Europe/Skopje'));

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = $this->createService(30);
        $date = CarbonImmutable::parse('2026-03-19', 'Europe/Skopje');

        BusinessHour::query()->create([
            'weekday' => $date->dayOfWeek,
            'is_active' => true,
            'start_time' => '09:00',
            'end_time' => '11:00',
        ]);

        Booking::query()->create([
            'user_id' => $client->id,
            'service_id' => $service->id,
            'starts_at' => CarbonImmutable::parse('2026-03-19 09:30:00', 'Europe/Skopje'),
            'ends_at' => CarbonImmutable::parse('2026-03-19 10:00:00', 'Europe/Skopje'),
            'status' => Booking::STATUS_BOOKED,
        ]);

        $response = $this->actingAs($client)->getJson(route('bookings.availability', [
            'service_id' => $service->id,
            'date' => $date->toDateString(),
        ]));

        $response->assertOk();

        $labels = array_column($response->json('slots'), 'label');

        $this->assertNotContains('09:30', $labels);
        $this->assertContains('09:00', $labels);
        $this->assertContains('10:00', $labels);
    }

    public function test_availability_excludes_slots_overlapping_blocked_period(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-18 07:00:00', 'Europe/Skopje'));

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = $this->createService(30);
        $date = CarbonImmutable::parse('2026-03-19', 'Europe/Skopje');

        BusinessHour::query()->create([
            'weekday' => $date->dayOfWeek,
            'is_active' => true,
            'start_time' => '09:00',
            'end_time' => '11:00',
        ]);

        BlockedPeriod::query()->create([
            'starts_at' => CarbonImmutable::parse('2026-03-19 10:00:00', 'Europe/Skopje'),
            'ends_at' => CarbonImmutable::parse('2026-03-19 10:30:00', 'Europe/Skopje'),
            'reason' => 'Maintenance',
        ]);

        $response = $this->actingAs($client)->getJson(route('bookings.availability', [
            'service_id' => $service->id,
            'date' => $date->toDateString(),
        ]));

        $response->assertOk();

        $labels = array_column($response->json('slots'), 'label');

        $this->assertNotContains('10:00', $labels);
        $this->assertContains('09:30', $labels);
    }

    public function test_availability_changes_when_service_changes(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-18 07:00:00', 'Europe/Skopje'));

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $shortService = $this->createService(15);
        $longService = $this->createService(30);
        $date = CarbonImmutable::parse('2026-03-19', 'Europe/Skopje');

        BusinessHour::query()->create([
            'weekday' => $date->dayOfWeek,
            'is_active' => true,
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);

        $shortResponse = $this->actingAs($client)->getJson(route('bookings.availability', [
            'service_id' => $shortService->id,
            'date' => $date->toDateString(),
        ]));

        $longResponse = $this->actingAs($client)->getJson(route('bookings.availability', [
            'service_id' => $longService->id,
            'date' => $date->toDateString(),
        ]));

        $shortResponse->assertOk();
        $longResponse->assertOk();

        $shortLabels = array_column($shortResponse->json('slots'), 'label');
        $longLabels = array_column($longResponse->json('slots'), 'label');

        $this->assertSame(['08:00', '08:15', '08:30', '08:45'], $shortLabels);
        $this->assertSame(['08:00', '08:30'], $longLabels);
        $this->assertNotSame($shortLabels, $longLabels);
    }

    public function test_availability_returns_empty_when_no_full_slot_fits(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-18 07:00:00', 'Europe/Skopje'));

        $client = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $service = $this->createService(45);
        $date = CarbonImmutable::parse('2026-03-19', 'Europe/Skopje');

        BusinessHour::query()->create([
            'weekday' => $date->dayOfWeek,
            'is_active' => true,
            'start_time' => '08:00',
            'end_time' => '08:30',
        ]);

        $response = $this->actingAs($client)->getJson(route('bookings.availability', [
            'service_id' => $service->id,
            'date' => $date->toDateString(),
        ]));

        $response->assertOk();
        $response->assertExactJson([
            'slots' => [],
        ]);
    }

    private function createService(int $durationMinutes): Service
    {
        return Service::query()->create([
            'name' => 'Test service '.$durationMinutes,
            'duration_minutes' => $durationMinutes,
            'price' => 500,
            'is_active' => true,
        ]);
    }
}

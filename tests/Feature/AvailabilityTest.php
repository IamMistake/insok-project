<?php

namespace Tests\Feature;

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

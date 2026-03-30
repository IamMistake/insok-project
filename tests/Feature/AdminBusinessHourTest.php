<?php

namespace Tests\Feature;

use App\Models\BusinessHour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBusinessHourTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_business_hours(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $payload = [
            'hours' => [
                0 => ['is_active' => '1', 'start_time' => '10:00', 'end_time' => '15:00'],
                1 => ['is_active' => '1', 'start_time' => '09:00', 'end_time' => '17:00'],
                2 => ['is_active' => '1', 'start_time' => '09:30', 'end_time' => '16:30'],
                3 => ['is_active' => '0', 'start_time' => '', 'end_time' => ''],
                4 => ['is_active' => '1', 'start_time' => '08:00', 'end_time' => '12:00'],
                5 => ['is_active' => '1', 'start_time' => '11:00', 'end_time' => '14:00'],
                6 => ['start_time' => '', 'end_time' => ''],
            ],
        ];

        $response = $this->actingAs($admin)->put(route('admin.business-hours.update'), $payload);

        $response->assertRedirect(route('admin.business-hours.index'));

        $this->actingAs($admin)
            ->get(route('admin.business-hours.index'))
            ->assertOk()
            ->assertSee('value="09:00"', false)
            ->assertSee('value="17:00"', false);

        $this->assertDatabaseHas('business_hours', [
            'weekday' => 1,
            'is_active' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        $this->assertDatabaseHas('business_hours', [
            'weekday' => 3,
            'is_active' => 0,
            'start_time' => null,
            'end_time' => null,
        ]);

        $this->assertDatabaseHas('business_hours', [
            'weekday' => 6,
            'is_active' => 0,
            'start_time' => null,
            'end_time' => null,
        ]);

        $this->assertSame(7, BusinessHour::query()->count());
    }
}

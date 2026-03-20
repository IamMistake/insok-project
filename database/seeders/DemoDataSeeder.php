<?php

namespace Database\Seeders;

use App\Models\BlockedPeriod;
use App\Models\Booking;
use App\Models\BusinessHour;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ],
        );

        $client = User::query()->updateOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Client User',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CLIENT,
            ],
        );

        $serviceA = Service::query()->updateOrCreate(
            ['name' => 'Konsultacija'],
            [
                'description' => 'Individualna konsultacija za izbor na usluga.',
                'duration_minutes' => 30,
                'price' => 1200,
                'is_active' => true,
            ],
        );

        $serviceB = Service::query()->updateOrCreate(
            ['name' => 'Masaza'],
            [
                'description' => 'Relaks masaza so trajanje od 60 minuti.',
                'duration_minutes' => 60,
                'price' => 1800,
                'is_active' => true,
            ],
        );

        foreach (range(0, 6) as $weekday) {
            $isWeekday = $weekday >= 1 && $weekday <= 5;

            BusinessHour::query()->updateOrCreate(
                ['weekday' => $weekday],
                [
                    'is_active' => $isWeekday,
                    'start_time' => $isWeekday ? '09:00' : null,
                    'end_time' => $isWeekday ? '17:00' : null,
                ],
            );
        }

        $blockedStart = CarbonImmutable::now()->addDays(2)->setTime(12, 0);

        BlockedPeriod::query()->updateOrCreate(
            [
                'starts_at' => $blockedStart,
                'ends_at' => $blockedStart->addHour(),
            ],
            [
                'reason' => 'Pauza',
            ],
        );

        $bookingStart = CarbonImmutable::now()->addDays(1)->setTime(10, 0);

        Booking::query()->updateOrCreate(
            [
                'user_id' => $client->id,
                'service_id' => $serviceA->id,
                'starts_at' => $bookingStart,
                'ends_at' => $bookingStart->addMinutes($serviceA->duration_minutes),
            ],
            [
                'status' => Booking::STATUS_BOOKED,
                'notes' => 'Primer rezervacija',
            ],
        );

        Booking::query()->updateOrCreate(
            [
                'user_id' => $client->id,
                'service_id' => $serviceB->id,
                'starts_at' => $bookingStart->addDays(1),
                'ends_at' => $bookingStart->addDays(1)->addMinutes($serviceB->duration_minutes),
            ],
            [
                'status' => Booking::STATUS_CANCELLED,
                'notes' => 'Primer otkazana rezervacija',
            ],
        );

        $admin->update(['email_verified_at' => now()]);
        $client->update(['email_verified_at' => now()]);
    }
}

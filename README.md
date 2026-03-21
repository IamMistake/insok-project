# Laravel Appointment Booking App

This project is a Laravel 13 application for **appointment booking with calendars and available slots**.

## Features

- Authentication and registration via Laravel Breeze (Blade).
- User roles:
  - `admin` manages services, business hours, blocked periods, and the admin calendar.
  - `client` views availability, books, reschedules, and cancels appointments.
- FullCalendar views for:
  - client bookings,
  - admin overview of bookings and blocks.
- Slot generation based on:
  - business hours,
  - service duration,
  - existing bookings,
  - blocked periods,
  - recurring blocked periods.
- Email notifications for booking creation, rescheduling, and cancellation.
- Validations to prevent overlap, out-of-hours bookings, and blocked time selections.

## Tech Stack

- Laravel 13
- PHP 8.3+
- SQLite
- Laravel Breeze (Blade)
- Tailwind CSS
- FullCalendar (CDN)

## Installation

1. Install dependencies:

```bash
composer install
npm install
```

2. Configure `.env` (SQLite is already prepared). Set the provider timezone if needed:

```
APP_TIMEZONE=Europe/Skopje
```

3. Generate key and prepare the database:

```bash
php artisan key:generate
php artisan migrate:fresh --seed
```

4. Run the application:

```bash
php artisan serve
npm run dev
```

## Demo Accounts

After seeding, the following users are created:

- Admin:
  - email: `admin@example.com`
  - password: `password`
- Client:
  - email: `client@example.com`
  - password: `password`

## Main Routes

- `/calendar` - client calendar and booking form.
- `/admin/calendar` - admin calendar.
- `/admin/services` - manage services.
- `/admin/business-hours` - business hours.
- `/admin/blocked-periods` - one-off blocked periods.
- `/admin/recurring-blocked-periods` - recurring blocks.

## Testing

```bash
php artisan test
```

Tests cover the core booking flow, overlap checks, and role access restrictions.

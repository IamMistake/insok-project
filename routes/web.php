<?php

use App\Http\Controllers\Admin\BlockedPeriodController;
use App\Http\Controllers\Admin\BusinessHourController;
use App\Http\Controllers\Admin\CalendarController as AdminCalendarController;
use App\Http\Controllers\Admin\RecurringBlockedPeriodController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : view('welcome');
});

Route::get('/dashboard', function () {
    $user = Auth::user();

    if ($user && $user->role === User::ROLE_ADMIN) {
        return redirect()->route('admin.calendar.index');
    }

    return redirect()->route('calendar.index');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:client'])->group(function () {
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
    Route::get('/availability', [BookingController::class, 'availability'])->name('bookings.availability');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::patch('/bookings/{booking}/reschedule', [BookingController::class, 'reschedule'])->name('bookings.reschedule');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/calendar', [AdminCalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [AdminCalendarController::class, 'events'])->name('calendar.events');

    Route::resource('services', ServiceController::class)->except(['show']);

    Route::get('/business-hours', [BusinessHourController::class, 'index'])->name('business-hours.index');
    Route::put('/business-hours', [BusinessHourController::class, 'update'])->name('business-hours.update');

    Route::resource('blocked-periods', BlockedPeriodController::class)->except(['show']);
    Route::resource('recurring-blocked-periods', RecurringBlockedPeriodController::class)->except(['show']);
});

require __DIR__.'/auth.php';

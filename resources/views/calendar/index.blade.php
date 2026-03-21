<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="rr-kicker mb-2">Client dashboard</div>
            <h2 class="rr-section-title text-[color:var(--rr-text)] leading-tight">My bookings and available slots</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rr-alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="rr-alert-error">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rr-panel lg:col-span-1">
                    <h3 class="text-xl text-[color:var(--rr-text)]">New booking</h3>

                    @if ($services->isEmpty())
                        <p class="mt-3 text-sm rr-muted">There are no active services right now.</p>
                    @else
                        <form id="booking-form" action="{{ route('bookings.store') }}" method="POST" class="mt-4 space-y-4">
                            @csrf

                            <div>
                                <x-input-label for="service_id" value="Service" />
                                <select id="service_id" name="service_id" class="rr-control" required>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>
                                            {{ $service->name }} ({{ $service->duration_minutes }} min)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="slot_date" value="Date" />
                                <x-text-input id="slot_date" type="date" class="mt-1 block w-full" value="{{ now()->format('Y-m-d') }}" required />
                            </div>

                            <div>
                                <div class="text-sm font-medium text-[color:var(--rr-muted)] uppercase tracking-[0.12em]">Available slots</div>
                                <div id="slot-list" class="mt-2 grid grid-cols-2 gap-2"></div>
                                <p id="slot-message" class="mt-2 text-sm rr-muted">Select a date and service to view available slots.</p>
                            </div>

                            <input id="starts_at" name="starts_at" type="hidden" value="{{ old('starts_at') }}">

                            <div>
                                <x-input-label for="notes" value="Notes (optional)" />
                                <textarea id="notes" name="notes" rows="3" class="rr-control">{{ old('notes') }}</textarea>
                            </div>

                            <x-primary-button>Book appointment</x-primary-button>
                        </form>
                    @endif
                </div>

                <div class="rr-panel lg:col-span-2">
                    <h3 class="mb-4 text-xl text-[color:var(--rr-text)]">My calendar</h3>
                    <div id="client-calendar" class="rr-calendar"></div>
                </div>
            </div>

            <div id="reschedule-panel" class="rr-panel hidden">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xl text-[color:var(--rr-text)]">Reschedule booking</h3>
                        <p id="reschedule-booking-label" class="mt-1 text-sm rr-muted"></p>
                    </div>
                    <button id="reschedule-close" type="button" class="text-sm rr-link">Close</button>
                </div>

                <form id="reschedule-form" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="reschedule_date" value="New date" />
                        <x-text-input id="reschedule_date" type="date" class="mt-1 block w-full max-w-xs" value="{{ now()->format('Y-m-d') }}" required />
                    </div>

                    <div>
                        <div class="text-sm font-medium uppercase tracking-[0.12em] text-[color:var(--rr-muted)]">Available slots for rescheduling</div>
                        <div id="reschedule-slot-list" class="mt-2 grid grid-cols-2 gap-2 md:grid-cols-4"></div>
                        <p id="reschedule-slot-message" class="mt-2 text-sm rr-muted">Select a booking to view available slots.</p>
                    </div>

                    <input id="reschedule_starts_at" name="starts_at" type="hidden">

                    <x-primary-button>Reschedule</x-primary-button>
                </form>
            </div>

            <div class="rr-table-wrap">
                <div class="border-b rr-divider p-6">
                    <h3 class="text-xl text-[color:var(--rr-text)]">Upcoming bookings</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="rr-table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y [--tw-divide-opacity:1] divide-[color:var(--rr-line)]">
                            @forelse ($upcomingBookings as $booking)
                                <tr>
                                    <td class="text-[color:var(--rr-text)]">{{ $booking->service?->name ?? '-' }}</td>
                                    <td class="rr-muted">{{ $booking->starts_at->format('d.m.Y H:i') }} - {{ $booking->ends_at->format('H:i') }}</td>
                                    <td>
                                        <span class="{{ $booking->status === \App\Models\Booking::STATUS_BOOKED ? 'rr-badge-success' : 'rr-badge-muted' }}">
                                            {{ $booking->status === \App\Models\Booking::STATUS_BOOKED ? 'Active' : 'Cancelled' }}
                                        </span>
                                    </td>
                                    <td class="text-right text-sm">
                                        @if ($booking->status === \App\Models\Booking::STATUS_BOOKED && $booking->starts_at->isFuture())
                                            <button
                                                type="button"
                                                class="rr-link"
                                                data-reschedule-button
                                                data-booking-id="{{ $booking->id }}"
                                                data-service-id="{{ $booking->service_id }}"
                                                data-service-name="{{ $booking->service?->name ?? 'Service' }}"
                                                data-start-date="{{ $booking->starts_at->format('Y-m-d') }}"
                                                data-start-label="{{ $booking->starts_at->format('d.m.Y H:i') }}"
                                            >Reschedule</button>

                                            <form method="POST" action="{{ route('bookings.destroy', $booking) }}" class="inline-block ml-3" onsubmit="return confirm('Do you want to cancel this booking?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-[color:var(--rr-danger)] transition hover:opacity-80">Cancel</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm rr-muted">You have no bookings.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css">
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const serviceSelect = document.getElementById('service_id');
                const dateInput = document.getElementById('slot_date');
                const startsAtInput = document.getElementById('starts_at');
                const slotList = document.getElementById('slot-list');
                const slotMessage = document.getElementById('slot-message');
                const bookingForm = document.getElementById('booking-form');
                const reschedulePanel = document.getElementById('reschedule-panel');
                const rescheduleForm = document.getElementById('reschedule-form');
                const rescheduleDate = document.getElementById('reschedule_date');
                const rescheduleStartsAtInput = document.getElementById('reschedule_starts_at');
                const rescheduleSlotList = document.getElementById('reschedule-slot-list');
                const rescheduleSlotMessage = document.getElementById('reschedule-slot-message');
                const rescheduleBookingLabel = document.getElementById('reschedule-booking-label');
                const rescheduleClose = document.getElementById('reschedule-close');
                const rescheduleButtons = document.querySelectorAll('[data-reschedule-button]');
                const appTimeZone = "{{ config('app.timezone') }}";
                const todayLocal = new Date();
                const todayValue = todayLocal.toLocaleDateString('en-CA', { timeZone: appTimeZone });

                let availabilityRequestId = 0;
                let rescheduleRequestId = 0;

                const availabilityUrl = "{{ route('bookings.availability') }}";
                let selectedBooking = null;

                if (dateInput) {
                    dateInput.value = todayValue;
                }

                if (rescheduleDate && !rescheduleDate.value) {
                    rescheduleDate.value = todayValue;
                }

                async function loadSlots() {
                    if (!serviceSelect || !dateInput || !slotList || !slotMessage) {
                        return;
                    }

                    const serviceId = serviceSelect.value;
                    const date = dateInput.value;

                    if (!serviceId || !date) {
                        slotList.innerHTML = '';
                        startsAtInput.value = '';
                        slotMessage.textContent = 'Select a date and service to view available slots.';
                        return;
                    }

                    slotMessage.textContent = 'Loading slots...';
                    slotList.innerHTML = '';
                    startsAtInput.value = '';

                    availabilityRequestId += 1;
                    const currentRequestId = availabilityRequestId;

                    const params = new URLSearchParams({
                        service_id: serviceId,
                        date,
                    });

                    const response = await fetch(`${availabilityUrl}?${params.toString()}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const payload = await response.json();
                    const slots = payload.slots || [];

                    if (currentRequestId !== availabilityRequestId) {
                        return;
                    }

                    if (!slots.length) {
                        startsAtInput.value = '';
                        slotMessage.textContent = 'No available slots for this day.';
                        slotList.innerHTML = '';
                        return;
                    }

                    slotMessage.textContent = 'Click a slot to select it.';

                    slots.forEach((slot) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.textContent = slot.label;
                        button.dataset.startsAt = slot.start;
                        button.className = 'rounded-2xl border border-[color:var(--rr-line)] bg-[rgba(255,255,255,0.54)] px-3 py-2 text-sm transition hover:bg-[rgba(159,122,75,0.1)] hover:border-[rgba(159,122,75,0.24)]';

                        button.addEventListener('click', () => {
                            startsAtInput.value = slot.start;

                            Array.from(slotList.querySelectorAll('button')).forEach((el) => {
                                el.classList.remove('bg-[#1f1c18]', 'text-white', 'border-[#1f1c18]');
                            });

                            button.classList.add('bg-[#1f1c18]', 'text-white', 'border-[#1f1c18]');
                            slotMessage.textContent = `Selected slot: ${slot.label}`;
                        });

                        slotList.appendChild(button);
                    });
                }

                async function loadRescheduleSlots() {
                    if (!selectedBooking || !rescheduleDate || !rescheduleSlotList || !rescheduleSlotMessage) {
                        return;
                    }

                    rescheduleSlotList.innerHTML = '';
                    rescheduleStartsAtInput.value = '';
                    rescheduleSlotMessage.textContent = 'Loading slots...';

                    rescheduleRequestId += 1;
                    const currentRequestId = rescheduleRequestId;

                    const params = new URLSearchParams({
                        service_id: selectedBooking.serviceId,
                        date: rescheduleDate.value,
                        ignore_booking_id: selectedBooking.bookingId,
                    });

                    const response = await fetch(`${availabilityUrl}?${params.toString()}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const payload = await response.json();
                    const slots = payload.slots || [];

                    if (currentRequestId !== rescheduleRequestId) {
                        return;
                    }

                    if (!slots.length) {
                        rescheduleSlotMessage.textContent = 'No available slots for this day.';
                        rescheduleSlotList.innerHTML = '';
                        return;
                    }

                    rescheduleSlotMessage.textContent = 'Click a slot to select it.';

                    slots.forEach((slot) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.textContent = slot.label;
                        button.className = 'rounded-2xl border border-[color:var(--rr-line)] bg-[rgba(255,255,255,0.54)] px-3 py-2 text-sm transition hover:bg-[rgba(159,122,75,0.1)] hover:border-[rgba(159,122,75,0.24)]';

                        button.addEventListener('click', () => {
                            rescheduleStartsAtInput.value = slot.start;

                            Array.from(rescheduleSlotList.querySelectorAll('button')).forEach((el) => {
                                el.classList.remove('bg-[#1f1c18]', 'text-white', 'border-[#1f1c18]');
                            });

                            button.classList.add('bg-[#1f1c18]', 'text-white', 'border-[#1f1c18]');
                            rescheduleSlotMessage.textContent = `Selected new slot: ${slot.label}`;
                        });

                        rescheduleSlotList.appendChild(button);
                    });
                }

                if (serviceSelect && dateInput) {
                    serviceSelect.addEventListener('change', loadSlots);
                    dateInput.addEventListener('change', loadSlots);
                    loadSlots();
                }

                if (bookingForm) {
                    bookingForm.addEventListener('submit', (event) => {
                        if (!startsAtInput.value) {
                            event.preventDefault();
                            alert('Select an available slot before booking.');
                        }
                    });
                }

                rescheduleButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        selectedBooking = {
                            bookingId: button.dataset.bookingId,
                            serviceId: button.dataset.serviceId,
                        };

                        rescheduleForm.action = `/bookings/${button.dataset.bookingId}/reschedule`;
                        rescheduleDate.value = button.dataset.startDate;
                        rescheduleBookingLabel.textContent = `${button.dataset.serviceName} - current slot ${button.dataset.startLabel}`;
                        reschedulePanel.classList.remove('hidden');
                        loadRescheduleSlots();
                        reschedulePanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                });

                if (rescheduleDate) {
                    rescheduleDate.addEventListener('change', loadRescheduleSlots);
                }

                if (rescheduleClose) {
                    rescheduleClose.addEventListener('click', () => {
                        reschedulePanel.classList.add('hidden');
                    });
                }

                if (rescheduleForm) {
                    rescheduleForm.addEventListener('submit', (event) => {
                        if (!rescheduleStartsAtInput.value) {
                            event.preventDefault();
                            alert('Select a new available slot before rescheduling.');
                        }
                    });
                }

                const calendarEl = document.getElementById('client-calendar');

                if (calendarEl) {
                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: window.innerWidth < 768 ? 'timeGridDay' : 'timeGridWeek',
                        height: 'auto',
                        firstDay: 1,
                        locale: 'en',
                        timeZone: appTimeZone,
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        eventDidMount: function (info) {
                            const start = info.event.start;
                            const end = info.event.end;

                            if (!start || !end) {
                                return;
                            }

                            const durationMinutes = (end.getTime() - start.getTime()) / 60000;

                            if (durationMinutes >= 15 && durationMinutes < 30) {
                                info.el.classList.add('rr-event-short');

                                const harness = info.el.parentElement;
                                if (harness) {
                                    harness.classList.add('rr-event-short-harness');
                                }
                            }
                        },
                        events: "{{ route('calendar.events') }}",
                    });

                    calendar.render();
                }
            });
        </script>
    @endpush
</x-app-layout>

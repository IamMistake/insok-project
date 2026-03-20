<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="rr-kicker mb-2">Client dashboard</div>
            <h2 class="rr-section-title text-[color:var(--rr-text)] leading-tight">Moe rezervacii i slobodni termini</h2>
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
                    <h3 class="text-xl text-[color:var(--rr-text)]">Nova rezervacija</h3>

                    @if ($services->isEmpty())
                        <p class="mt-3 text-sm rr-muted">Momentalno nema aktivni uslugi.</p>
                    @else
                        <form id="booking-form" action="{{ route('bookings.store') }}" method="POST" class="mt-4 space-y-4">
                            @csrf

                            <div>
                                <x-input-label for="service_id" value="Usluga" />
                                <select id="service_id" name="service_id" class="rr-control" required>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>
                                            {{ $service->name }} ({{ $service->duration_minutes }} min)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="slot_date" value="Datum" />
                                <x-text-input id="slot_date" type="date" class="mt-1 block w-full" value="{{ now()->format('Y-m-d') }}" required />
                            </div>

                            <div>
                                <div class="text-sm font-medium text-[color:var(--rr-muted)] uppercase tracking-[0.12em]">Slobodni termini</div>
                                <div id="slot-list" class="mt-2 grid grid-cols-2 gap-2"></div>
                                <p id="slot-message" class="mt-2 text-sm rr-muted">Izberete datum i usluga za prikaz na termini.</p>
                            </div>

                            <input id="starts_at" name="starts_at" type="hidden" value="{{ old('starts_at') }}">

                            <div>
                                <x-input-label for="notes" value="Zabeleska (opcionalno)" />
                                <textarea id="notes" name="notes" rows="3" class="rr-control">{{ old('notes') }}</textarea>
                            </div>

                            <x-primary-button>Rezerviraj termin</x-primary-button>
                        </form>
                    @endif
                </div>

                <div class="rr-panel lg:col-span-2">
                    <h3 class="mb-4 text-xl text-[color:var(--rr-text)]">Moj kalendar</h3>
                    <div id="client-calendar" class="rr-calendar"></div>
                </div>
            </div>

            <div id="reschedule-panel" class="rr-panel hidden">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xl text-[color:var(--rr-text)]">Prezakazi rezervacija</h3>
                        <p id="reschedule-booking-label" class="mt-1 text-sm rr-muted"></p>
                    </div>
                    <button id="reschedule-close" type="button" class="text-sm rr-link">Zatvori</button>
                </div>

                <form id="reschedule-form" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="reschedule_date" value="Nov datum" />
                        <x-text-input id="reschedule_date" type="date" class="mt-1 block w-full max-w-xs" value="{{ now()->format('Y-m-d') }}" required />
                    </div>

                    <div>
                        <div class="text-sm font-medium uppercase tracking-[0.12em] text-[color:var(--rr-muted)]">Slobodni termini za prezakazuvanje</div>
                        <div id="reschedule-slot-list" class="mt-2 grid grid-cols-2 gap-2 md:grid-cols-4"></div>
                        <p id="reschedule-slot-message" class="mt-2 text-sm rr-muted">Izberete rezervacija za da se prikazat termini.</p>
                    </div>

                    <input id="reschedule_starts_at" name="starts_at" type="hidden">

                    <x-primary-button>Prezakazi</x-primary-button>
                </form>
            </div>

            <div class="rr-table-wrap">
                <div class="border-b rr-divider p-6">
                    <h3 class="text-xl text-[color:var(--rr-text)]">Pretstojni rezervacii</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="rr-table">
                        <thead>
                            <tr>
                                <th>Usluga</th>
                                <th>Termin</th>
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
                                            {{ $booking->status === \App\Models\Booking::STATUS_BOOKED ? 'Aktivna' : 'Otkazana' }}
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
                                                data-service-name="{{ $booking->service?->name ?? 'Usluga' }}"
                                                data-start-date="{{ $booking->starts_at->format('Y-m-d') }}"
                                                data-start-label="{{ $booking->starts_at->format('d.m.Y H:i') }}"
                                            >Prezakazi</button>

                                            <form method="POST" action="{{ route('bookings.destroy', $booking) }}" class="inline-block ml-3" onsubmit="return confirm('Dali sakate da ja otkazete rezervacijata?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-[color:var(--rr-danger)] transition hover:opacity-80">Otkazi</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm rr-muted">Nemate rezervacii.</td>
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

                const availabilityUrl = "{{ route('bookings.availability') }}";
                let selectedBooking = null;

                async function loadSlots() {
                    if (!serviceSelect || !dateInput || !slotList || !slotMessage) {
                        return;
                    }

                    const serviceId = serviceSelect.value;
                    const date = dateInput.value;

                    if (!serviceId || !date) {
                        slotList.innerHTML = '';
                        startsAtInput.value = '';
                        slotMessage.textContent = 'Izberete datum i usluga za prikaz na termini.';
                        return;
                    }

                    slotMessage.textContent = 'Se vcituvaat termini...';
                    slotList.innerHTML = '';

                    const response = await fetch(`${availabilityUrl}?service_id=${serviceId}&date=${date}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const payload = await response.json();
                    const slots = payload.slots || [];

                    if (!slots.length) {
                        startsAtInput.value = '';
                        slotMessage.textContent = 'Nema slobodni termini za ovoj den.';
                        return;
                    }

                    slotMessage.textContent = 'Kliknete na termin za izbor.';

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
                            slotMessage.textContent = `Izbran termin: ${slot.label}`;
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
                    rescheduleSlotMessage.textContent = 'Se vcituvaat termini...';

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

                    if (!slots.length) {
                        rescheduleSlotMessage.textContent = 'Nema slobodni termini za ovoj den.';
                        return;
                    }

                    rescheduleSlotMessage.textContent = 'Kliknete na termin za izbor.';

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
                            rescheduleSlotMessage.textContent = `Izbran nov termin: ${slot.label}`;
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
                            alert('Izberete sloboden termin pred rezervacija.');
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
                        rescheduleBookingLabel.textContent = `${button.dataset.serviceName} - tekoven termin ${button.dataset.startLabel}`;
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
                            alert('Izberete nov sloboden termin pred prezakazuvanje.');
                        }
                    });
                }

                const calendarEl = document.getElementById('client-calendar');

                if (calendarEl) {
                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: window.innerWidth < 768 ? 'timeGridDay' : 'timeGridWeek',
                        height: 'auto',
                        firstDay: 1,
                        locale: 'mk',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        events: "{{ route('calendar.events') }}",
                    });

                    calendar.render();
                }
            });
        </script>
    @endpush
</x-app-layout>

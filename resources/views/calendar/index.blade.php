<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Moe rezervacii i slobodni termini</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-100 p-4 text-green-700">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-100 p-4 text-red-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white shadow-sm sm:rounded-lg p-6 lg:col-span-1">
                    <h3 class="text-lg font-semibold text-gray-900">Nova rezervacija</h3>

                    @if ($services->isEmpty())
                        <p class="mt-3 text-sm text-gray-600">Momentalno nema aktivni uslugi.</p>
                    @else
                        <form id="booking-form" action="{{ route('bookings.store') }}" method="POST" class="mt-4 space-y-4">
                            @csrf

                            <div>
                                <x-input-label for="service_id" value="Usluga" />
                                <select id="service_id" name="service_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
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
                                <div class="text-sm font-medium text-gray-700">Slobodni termini</div>
                                <div id="slot-list" class="mt-2 grid grid-cols-2 gap-2"></div>
                                <p id="slot-message" class="mt-2 text-sm text-gray-500">Izberete datum i usluga za prikaz na termini.</p>
                            </div>

                            <input id="starts_at" name="starts_at" type="hidden" value="{{ old('starts_at') }}">

                            <div>
                                <x-input-label for="notes" value="Zabeleska (opcionalno)" />
                                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                            </div>

                            <x-primary-button>Rezerviraj termin</x-primary-button>
                        </form>
                    @endif
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-4 sm:p-6 lg:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Moj kalendar</h3>
                    <div id="client-calendar"></div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Pretstojni rezervacii</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usluga</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Termin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($upcomingBookings as $booking)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $booking->service?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $booking->starts_at->format('d.m.Y H:i') }} - {{ $booking->ends_at->format('H:i') }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $booking->status === \App\Models\Booking::STATUS_BOOKED ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $booking->status === \App\Models\Booking::STATUS_BOOKED ? 'Aktivna' : 'Otkazana' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        @if ($booking->status === \App\Models\Booking::STATUS_BOOKED && $booking->starts_at->isFuture())
                                            <form method="POST" action="{{ route('bookings.destroy', $booking) }}" onsubmit="return confirm('Dali sakate da ja otkazete rezervacijata?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-red-600 hover:text-red-900">Otkazi</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">Nemate rezervacii.</td>
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

                const availabilityUrl = "{{ route('bookings.availability') }}";

                async function loadSlots() {
                    if (!serviceSelect || !dateInput || !slotList || !slotMessage) {
                        return;
                    }

                    const serviceId = serviceSelect.value;
                    const date = dateInput.value;

                    if (!serviceId || !date) {
                        slotList.innerHTML = '';
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
                        slotMessage.textContent = 'Nema slobodni termini za ovoj den.';
                        return;
                    }

                    slotMessage.textContent = 'Kliknete na termin za izbor.';

                    slots.forEach((slot) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.textContent = slot.label;
                        button.dataset.startsAt = slot.start;
                        button.className = 'rounded-md border border-gray-300 px-3 py-2 text-sm hover:bg-indigo-50 hover:border-indigo-500';

                        button.addEventListener('click', () => {
                            startsAtInput.value = slot.start;

                            Array.from(slotList.querySelectorAll('button')).forEach((el) => {
                                el.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-600');
                            });

                            button.classList.add('bg-indigo-600', 'text-white', 'border-indigo-600');
                            slotMessage.textContent = `Izbran termin: ${slot.label}`;
                        });

                        slotList.appendChild(button);
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

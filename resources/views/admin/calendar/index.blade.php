<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="rr-kicker mb-2">Admin overview</div>
            <h2 class="rr-section-title text-[color:var(--rr-text)] leading-tight">Admin kalendar na rezervacii</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="rr-panel flex flex-wrap items-center gap-4 text-sm">
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-teal-700"></span> Rezerviran termin</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-red-600"></span> Blokiran termin</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-orange-500"></span> Povtorliva blokada</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-[rgba(98,91,82,0.9)]"></span> Otkazan termin</span>
            </div>

            <div class="rr-panel p-4 sm:p-6">
                <div id="admin-calendar" class="rr-calendar"></div>
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
                const calendarEl = document.getElementById('admin-calendar');

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
                    events: "{{ route('admin.calendar.events') }}",
                });

                calendar.render();
            });
        </script>
    @endpush
</x-app-layout>

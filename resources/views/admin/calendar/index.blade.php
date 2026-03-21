<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="rr-kicker mb-2">Admin overview</div>
            <h2 class="rr-section-title text-[color:var(--rr-text)] leading-tight">Admin booking calendar</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="rr-panel flex flex-wrap items-center gap-4 text-sm">
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-teal-700"></span> Booked slot</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-red-600"></span> Blocked slot</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-orange-500"></span> Recurring block</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-[rgba(98,91,82,0.9)]"></span> Cancelled slot</span>
            </div>

            <div class="rr-panel p-4 sm:p-6">
                <div id="admin-calendar" class="rr-calendar"></div>
            </div>
        </div>
    </div>

    <x-modal name="admin-calendar-event-details" maxWidth="lg">
        <div class="rr-event-modal">
            <div class="rr-event-modal__header">
                <div>
                    <p class="rr-kicker">Event details</p>
                    <h3 id="admin-event-title" class="rr-event-modal__title">Event</h3>
                </div>
                <button type="button" class="rr-event-modal__close" x-on:click="$dispatch('close-modal', 'admin-calendar-event-details')">Close</button>
            </div>

            <div class="rr-event-modal__meta">
                <span id="admin-event-status" class="rr-badge rr-hidden"></span>
                <span id="admin-event-type" class="rr-badge rr-hidden"></span>
            </div>

            <div class="rr-event-modal__grid">
                <div class="rr-event-field" data-field="service">
                    <div class="rr-event-field__label">Service</div>
                    <div id="admin-event-service" class="rr-event-field__value"></div>
                </div>
                <div class="rr-event-field" data-field="client">
                    <div class="rr-event-field__label">Client</div>
                    <div id="admin-event-client" class="rr-event-field__value"></div>
                </div>
                <div class="rr-event-field" data-field="description">
                    <div class="rr-event-field__label">Description</div>
                    <div id="admin-event-description" class="rr-event-field__value"></div>
                </div>
                <div class="rr-event-field" data-field="notes">
                    <div class="rr-event-field__label">Notes</div>
                    <div id="admin-event-notes" class="rr-event-field__value"></div>
                </div>
                <div class="rr-event-field" data-field="reason">
                    <div class="rr-event-field__label">Reason</div>
                    <div id="admin-event-reason" class="rr-event-field__value"></div>
                </div>
                <div class="rr-event-field" data-field="date">
                    <div class="rr-event-field__label">Date</div>
                    <div id="admin-event-date" class="rr-event-field__value"></div>
                </div>
                <div class="rr-event-field" data-field="time">
                    <div class="rr-event-field__label">Time</div>
                    <div id="admin-event-time" class="rr-event-field__value"></div>
                </div>
                <div class="rr-event-field" data-field="duration">
                    <div class="rr-event-field__label">Duration</div>
                    <div id="admin-event-duration" class="rr-event-field__value"></div>
                </div>
            </div>
        </div>
    </x-modal>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css">
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const calendarEl = document.getElementById('admin-calendar');
                const appTimeZone = "{{ config('app.timezone') }}";

                const dateFormatter = new Intl.DateTimeFormat('en-GB', {
                    timeZone: appTimeZone,
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });

                const timeFormatter = new Intl.DateTimeFormat('en-GB', {
                    timeZone: appTimeZone,
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const titleEl = document.getElementById('admin-event-title');
                const serviceEl = document.getElementById('admin-event-service');
                const clientEl = document.getElementById('admin-event-client');
                const descriptionEl = document.getElementById('admin-event-description');
                const notesEl = document.getElementById('admin-event-notes');
                const reasonEl = document.getElementById('admin-event-reason');
                const dateEl = document.getElementById('admin-event-date');
                const timeEl = document.getElementById('admin-event-time');
                const durationEl = document.getElementById('admin-event-duration');
                const statusEl = document.getElementById('admin-event-status');
                const typeEl = document.getElementById('admin-event-type');
                const modalFields = document.querySelectorAll('.rr-event-modal .rr-event-field');

                const setFieldValue = (fieldKey, element, value) => {
                    const field = document.querySelector(`.rr-event-modal .rr-event-field[data-field="${fieldKey}"]`);
                    if (!field || !element) {
                        return;
                    }

                    if (value) {
                        element.textContent = value;
                        field.classList.remove('rr-hidden');
                    } else {
                        element.textContent = '';
                        field.classList.add('rr-hidden');
                    }
                };

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
                        eventClick: function (info) {
                            const event = info.event;
                            const props = event.extendedProps || {};
                            const start = event.start;
                            const end = event.end;
                            const durationMinutes = start && end ? Math.round((end.getTime() - start.getTime()) / 60000) : null;

                            if (titleEl) {
                                titleEl.textContent = event.title || 'Event';
                            }

                            modalFields.forEach((field) => field.classList.add('rr-hidden'));

                            setFieldValue('service', serviceEl, props.service_name || null);
                            setFieldValue('client', clientEl, props.client_name || null);
                            setFieldValue('description', descriptionEl, props.service_description || null);
                            setFieldValue('notes', notesEl, props.notes || null);
                            setFieldValue('reason', reasonEl, props.reason || null);
                            setFieldValue('date', dateEl, start ? dateFormatter.format(start) : null);
                            if (start && end) {
                                setFieldValue('time', timeEl, `${timeFormatter.format(start)} - ${timeFormatter.format(end)}`);
                            } else {
                                setFieldValue('time', timeEl, null);
                            }
                            setFieldValue('duration', durationEl, durationMinutes ? `${durationMinutes} min` : (props.duration_minutes ? `${props.duration_minutes} min` : null));

                            if (statusEl) {
                                const statusLabel = props.status_label || props.status || null;
                                if (statusLabel) {
                                    statusEl.textContent = statusLabel;
                                    statusEl.classList.remove('rr-hidden');
                                    statusEl.classList.remove('rr-badge-danger', 'rr-badge-warning', 'rr-badge-muted', 'rr-badge-success');
                                    if (props.status === 'cancelled') {
                                        statusEl.classList.add('rr-badge-muted');
                                    } else {
                                        statusEl.classList.add('rr-badge-success');
                                    }
                                } else {
                                    statusEl.textContent = '';
                                    statusEl.classList.add('rr-hidden');
                                }
                            }

                            if (typeEl) {
                                const typeLabelMap = {
                                    booking: 'Booking',
                                    blocked: 'Blocked slot',
                                    recurring_blocked: 'Recurring block'
                                };
                                const typeLabel = typeLabelMap[props.type] || null;
                                if (typeLabel) {
                                    typeEl.textContent = typeLabel;
                                    typeEl.classList.remove('rr-hidden');
                                    typeEl.classList.remove('rr-badge-danger', 'rr-badge-warning', 'rr-badge-muted', 'rr-badge-success');
                                    if (props.type === 'blocked') {
                                        typeEl.classList.add('rr-badge-danger');
                                    } else if (props.type === 'recurring_blocked') {
                                        typeEl.classList.add('rr-badge-warning');
                                    } else {
                                        typeEl.classList.add('rr-badge-success');
                                    }
                                } else {
                                    typeEl.textContent = '';
                                    typeEl.classList.add('rr-hidden');
                                }
                            }

                            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'admin-calendar-event-details' }));
                        },
                    events: "{{ route('admin.calendar.events') }}",
                });

                calendar.render();
            });
        </script>
    @endpush
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="rr-kicker mb-2">Availability control</div>
            <h2 class="rr-section-title text-[color:var(--rr-text)] leading-tight">Rabotno vreme</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
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

            <div class="rr-panel">
                <form action="{{ route('admin.business-hours.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="space-y-3">
                        @foreach ($dayLabels as $weekday => $label)
                            @php
                                $hour = $hours->get($weekday);
                                $isActive = old("hours.$weekday.is_active", $hour?->is_active);
                            @endphp

                            <div class="grid gap-3 rounded-[1.25rem] border border-[color:var(--rr-line)] bg-[rgba(255,255,255,0.46)] p-4 sm:grid-cols-4 sm:items-center">
                                <label class="inline-flex items-center gap-2 sm:col-span-1">
                                    <input type="checkbox" name="hours[{{ $weekday }}][is_active]" value="1" class="rounded border-[color:var(--rr-line)] text-[color:var(--rr-accent)] shadow-sm focus:ring-[rgba(159,122,75,0.2)]" @checked($isActive)>
                                    <span class="text-sm font-medium text-[color:var(--rr-text)]">{{ $label }}</span>
                                </label>

                                <div>
                                    <x-input-label for="start_{{ $weekday }}" value="Pocetok" />
                                    <input id="start_{{ $weekday }}" type="time" name="hours[{{ $weekday }}][start_time]" value="{{ old("hours.$weekday.start_time", $hour?->start_time ? substr($hour->start_time, 0, 5) : '') }}" class="rr-control">
                                </div>

                                <div>
                                    <x-input-label for="end_{{ $weekday }}" value="Kraj" />
                                    <input id="end_{{ $weekday }}" type="time" name="hours[{{ $weekday }}][end_time]" value="{{ old("hours.$weekday.end_time", $hour?->end_time ? substr($hour->end_time, 0, 5) : '') }}" class="rr-control">
                                </div>

                                <div class="text-sm rr-muted sm:text-right">{{ $isActive ? 'Aktiven den' : 'Neaktiven den' }}</div>
                            </div>
                        @endforeach
                    </div>

                    <x-primary-button>Zacuvaj rabotno vreme</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

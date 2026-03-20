<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rabotno vreme</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
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

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.business-hours.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="space-y-3">
                        @foreach ($dayLabels as $weekday => $label)
                            @php
                                $hour = $hours->get($weekday);
                                $isActive = old("hours.$weekday.is_active", $hour?->is_active);
                            @endphp

                            <div class="grid gap-3 rounded-lg border border-gray-200 p-4 sm:grid-cols-4 sm:items-center">
                                <label class="inline-flex items-center gap-2 sm:col-span-1">
                                    <input type="checkbox" name="hours[{{ $weekday }}][is_active]" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked($isActive)>
                                    <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                                </label>

                                <div>
                                    <x-input-label for="start_{{ $weekday }}" value="Pocetok" />
                                    <input id="start_{{ $weekday }}" type="time" name="hours[{{ $weekday }}][start_time]" value="{{ old("hours.$weekday.start_time", $hour?->start_time ? substr($hour->start_time, 0, 5) : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <x-input-label for="end_{{ $weekday }}" value="Kraj" />
                                    <input id="end_{{ $weekday }}" type="time" name="hours[{{ $weekday }}][end_time]" value="{{ old("hours.$weekday.end_time", $hour?->end_time ? substr($hour->end_time, 0, 5) : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div class="text-sm text-gray-500 sm:text-right">{{ $isActive ? 'Aktiven den' : 'Neaktiven den' }}</div>
                            </div>
                        @endforeach
                    </div>

                    <x-primary-button>Zacuvaj rabotno vreme</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

@php
    $blockedPeriod = $blockedPeriod ?? null;
@endphp

@csrf

@if (($method ?? 'POST') !== 'POST')
    @method($method)
@endif

<div class="space-y-4">
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="starts_at" value="Pocetok" />
            <x-text-input id="starts_at" name="starts_at" type="datetime-local" class="mt-1 block w-full" :value="old('starts_at', $blockedPeriod?->starts_at?->format('Y-m-d\TH:i'))" required />
            <x-input-error :messages="$errors->get('starts_at')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="ends_at" value="Kraj" />
            <x-text-input id="ends_at" name="ends_at" type="datetime-local" class="mt-1 block w-full" :value="old('ends_at', $blockedPeriod?->ends_at?->format('Y-m-d\TH:i'))" required />
            <x-input-error :messages="$errors->get('ends_at')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="reason" value="Pricina" />
        <x-text-input id="reason" name="reason" type="text" class="mt-1 block w-full" :value="old('reason', $blockedPeriod?->reason)" />
        <x-input-error :messages="$errors->get('reason')" class="mt-2" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>Zacuvaj</x-primary-button>
        <a href="{{ route('admin.blocked-periods.index') }}" class="text-sm rr-link">Nazad</a>
    </div>
</div>

@csrf

@if (($method ?? 'POST') !== 'POST')
    @method($method)
@endif

<div class="space-y-4">
    <div>
        <x-input-label for="weekday" value="Den" />
        <select id="weekday" name="weekday" class="rr-control" required>
            @foreach ($dayLabels as $weekdayValue => $label)
                <option value="{{ $weekdayValue }}" @selected(old('weekday', $blockedPeriod->weekday ?? null) == $weekdayValue)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="start_time" value="Pocetok" />
            <x-text-input id="start_time" name="start_time" type="time" class="mt-1 block w-full" :value="old('start_time', isset($blockedPeriod) ? substr($blockedPeriod->start_time, 0, 5) : '')" required />
        </div>

        <div>
            <x-input-label for="end_time" value="Kraj" />
            <x-text-input id="end_time" name="end_time" type="time" class="mt-1 block w-full" :value="old('end_time', isset($blockedPeriod) ? substr($blockedPeriod->end_time, 0, 5) : '')" required />
        </div>
    </div>

    <div>
        <x-input-label for="reason" value="Pricina" />
        <x-text-input id="reason" name="reason" type="text" class="mt-1 block w-full" :value="old('reason', $blockedPeriod->reason ?? '')" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="effective_from" value="Vazi od" />
            <x-text-input id="effective_from" name="effective_from" type="date" class="mt-1 block w-full" :value="old('effective_from', isset($blockedPeriod) && $blockedPeriod->effective_from ? $blockedPeriod->effective_from->format('Y-m-d') : '')" />
        </div>

        <div>
            <x-input-label for="effective_until" value="Vazi do" />
            <x-text-input id="effective_until" name="effective_until" type="date" class="mt-1 block w-full" :value="old('effective_until', isset($blockedPeriod) && $blockedPeriod->effective_until ? $blockedPeriod->effective_until->format('Y-m-d') : '')" />
        </div>
    </div>

    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" class="rounded border-[color:var(--rr-line)] text-[color:var(--rr-accent)] shadow-sm focus:ring-[rgba(159,122,75,0.2)]" @checked(old('is_active', $blockedPeriod->is_active ?? true))>
        <span class="text-sm rr-muted">Aktivna povtorliva blokada</span>
    </label>

    @if ($errors->any())
        <div class="rr-alert-error">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex items-center gap-3">
        <x-primary-button>Zacuvaj</x-primary-button>
        <a href="{{ route('admin.recurring-blocked-periods.index') }}" class="text-sm rr-link">Nazad</a>
    </div>
</div>

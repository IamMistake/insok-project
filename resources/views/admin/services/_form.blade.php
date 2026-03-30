@php
    $service = $service ?? null;
@endphp

@csrf

@if (($method ?? 'POST') !== 'POST')
    @method($method)
@endif

<div class="space-y-4">
    <div>
        <x-input-label for="name" value="Service name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $service?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" class="rr-control" rows="4">{{ old('description', $service?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="duration_minutes" value="Duration (minutes)" />
            <x-text-input id="duration_minutes" name="duration_minutes" type="number" min="15" step="5" class="mt-1 block w-full" :value="old('duration_minutes', $service?->duration_minutes)" required />
            <x-input-error :messages="$errors->get('duration_minutes')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="price" value="Price (MKD)" />
            <x-text-input id="price" name="price" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('price', $service?->price)" required />
            <x-input-error :messages="$errors->get('price')" class="mt-2" />
        </div>
    </div>

    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" class="rounded border-[color:var(--rr-line)] text-[color:var(--rr-accent)] shadow-sm focus:ring-[rgba(159,122,75,0.2)]" @checked(old('is_active', $service?->is_active ?? true))>
        <span class="text-sm rr-muted">Active service</span>
    </label>

    <div class="flex items-center gap-3">
        <x-primary-button>Save</x-primary-button>
        <a href="{{ route('admin.services.index') }}" class="text-sm rr-link">Back</a>
    </div>
</div>

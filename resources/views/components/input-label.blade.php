@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium uppercase tracking-[0.12em] text-[color:var(--rr-muted)]']) }}>
    {{ $value ?? $slot }}
</label>

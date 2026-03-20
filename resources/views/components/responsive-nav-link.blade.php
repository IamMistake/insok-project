@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-2xl border border-[rgba(159,122,75,0.24)] bg-[rgba(159,122,75,0.12)] px-4 py-3 text-start text-base font-medium text-[color:var(--rr-text)] transition duration-150 ease-in-out'
            : 'block w-full rounded-2xl border border-transparent px-4 py-3 text-start text-base font-medium text-[color:var(--rr-muted)] transition duration-150 ease-in-out hover:border-[color:var(--rr-line)] hover:bg-[rgba(255,255,255,0.45)] hover:text-[color:var(--rr-text)]';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

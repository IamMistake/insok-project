@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-full border px-4 py-2 text-sm font-medium text-[color:var(--rr-text)] transition duration-150 ease-in-out bg-[rgba(159,122,75,0.12)] border-[rgba(159,122,75,0.24)] shadow-[0_8px_22px_rgba(30,24,18,0.04)]'
            : 'inline-flex items-center rounded-full border border-transparent px-4 py-2 text-sm font-medium text-[color:var(--rr-muted)] transition duration-150 ease-in-out hover:border-[color:var(--rr-line)] hover:bg-[rgba(255,255,255,0.45)] hover:text-[color:var(--rr-text)]';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-2xl border border-[color:var(--rr-line)] bg-[rgba(255,255,255,0.72)] px-4 py-3 text-sm text-[color:var(--rr-text)] shadow-[0_10px_24px_rgba(30,24,18,0.04)] placeholder:text-[color:var(--rr-muted)] focus:border-[rgba(159,122,75,0.45)] focus:ring-[rgba(159,122,75,0.2)]']) }}>

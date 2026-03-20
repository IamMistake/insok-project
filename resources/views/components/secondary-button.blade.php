<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center rounded-full border border-[color:var(--rr-line)] bg-[rgba(255,255,255,0.68)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em] text-[color:var(--rr-text)] shadow-[0_18px_44px_rgba(30,24,18,0.06)] transition duration-150 hover:-translate-y-0.5 hover:bg-[rgba(159,122,75,0.1)] focus:outline-none focus:ring-2 focus:ring-[rgba(159,122,75,0.35)] focus:ring-offset-2 disabled:opacity-25']) }}>
    {{ $slot }}
</button>

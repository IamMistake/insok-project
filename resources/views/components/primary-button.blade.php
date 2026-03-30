<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-full border border-transparent bg-[#1f1c18] px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em] text-white shadow-[0_14px_32px_rgba(31,28,24,0.18)] transition duration-150 hover:-translate-y-0.5 hover:bg-[#2a2621] focus:outline-none focus:ring-2 focus:ring-[rgba(159,122,75,0.45)] focus:ring-offset-2']) }}>
    {{ $slot }}
</button>

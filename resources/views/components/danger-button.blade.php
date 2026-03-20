<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-full border border-transparent bg-[#a14636] px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em] text-white shadow-[0_14px_32px_rgba(161,70,54,0.18)] transition duration-150 hover:-translate-y-0.5 hover:bg-[#8f3c2f] focus:outline-none focus:ring-2 focus:ring-[rgba(161,70,54,0.35)] focus:ring-offset-2']) }}>
    {{ $slot }}
</button>

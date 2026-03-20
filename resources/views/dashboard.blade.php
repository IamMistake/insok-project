<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="rr-kicker mb-2">Overview</div>
            <h2 class="rr-section-title text-[color:var(--rr-text)] leading-tight">{{ __('Dashboard') }}</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="rr-panel">
                <div class="text-[color:var(--rr-text)]">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

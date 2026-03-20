<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="rr-kicker mb-2">Availability control</div>
            <h2 class="rr-section-title text-[color:var(--rr-text)] leading-tight">Dodadi blokiran termin</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="rr-panel">
                <form method="POST" action="{{ route('admin.blocked-periods.store') }}">
                    @include('admin.blocked-periods._form', ['method' => 'POST'])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

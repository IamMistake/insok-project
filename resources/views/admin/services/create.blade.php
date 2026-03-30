<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="rr-kicker mb-2">Admin setup</div>
            <h2 class="rr-section-title text-[color:var(--rr-text)] leading-tight">New service</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="rr-panel">
                <form method="POST" action="{{ route('admin.services.store') }}">
                    @include('admin.services._form', ['method' => 'POST'])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Izmeni blokiran termin</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.blocked-periods.update', $blockedPeriod) }}">
                    @include('admin.blocked-periods._form', ['blockedPeriod' => $blockedPeriod, 'method' => 'PUT'])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

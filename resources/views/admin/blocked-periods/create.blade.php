<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dodadi blokiran termin</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.blocked-periods.store') }}">
                    @include('admin.blocked-periods._form', ['method' => 'POST'])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

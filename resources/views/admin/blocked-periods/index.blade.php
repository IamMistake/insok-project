<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Blokirani termini</h2>
            <a href="{{ route('admin.blocked-periods.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Dodadi blokada
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-100 p-4 text-green-700">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pocetok</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kraj</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pricina</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($blockedPeriods as $blockedPeriod)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $blockedPeriod->starts_at->format('d.m.Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $blockedPeriod->ends_at->format('d.m.Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $blockedPeriod->reason ?: '-' }}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route('admin.blocked-periods.edit', $blockedPeriod) }}" class="text-indigo-600 hover:text-indigo-900">Izmeni</a>

                                    <form action="{{ route('admin.blocked-periods.destroy', $blockedPeriod) }}" method="POST" class="inline-block ml-3" onsubmit="return confirm('Dali ste sigurni?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:text-red-900">Izbrisi</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">Nema definirani blokirani termini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

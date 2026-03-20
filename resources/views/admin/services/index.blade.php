<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="rr-kicker mb-2">Admin setup</div>
                <h2 class="rr-section-title text-[color:var(--rr-text)] leading-tight">Upravljanje so uslugi</h2>
            </div>
            <a href="{{ route('admin.services.create') }}" class="ghost-button !no-underline">
                Nova usluga
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rr-alert-success">{{ session('status') }}</div>
            @endif

            <div class="rr-table-wrap">
                <table class="rr-table">
                    <thead>
                        <tr>
                            <th>Usluga</th>
                            <th>Traenje</th>
                            <th>Cena</th>
                            <th>Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y [--tw-divide-opacity:1] divide-[color:var(--rr-line)]">
                        @forelse ($services as $service)
                            <tr>
                                <td>
                                    <div class="font-medium text-[color:var(--rr-text)]">{{ $service->name }}</div>
                                    <div class="text-sm rr-muted">{{ $service->description }}</div>
                                </td>
                                <td class="rr-muted">{{ $service->duration_minutes }} min</td>
                                <td class="rr-muted">{{ number_format((float) $service->price, 2) }} MKD</td>
                                <td>
                                    <span class="{{ $service->is_active ? 'rr-badge-success' : 'rr-badge-muted' }}">
                                        {{ $service->is_active ? 'Aktivna' : 'Neaktivna' }}
                                    </span>
                                </td>
                                <td class="text-right text-sm">
                                    <a href="{{ route('admin.services.edit', $service) }}" class="rr-link">Izmeni</a>

                                    <form action="{{ route('admin.services.destroy', $service) }}" method="POST" class="inline-block ml-3" onsubmit="return confirm('Dali ste sigurni?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-[color:var(--rr-danger)] transition hover:opacity-80">Izbrisi</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm rr-muted">Nema vneseni uslugi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

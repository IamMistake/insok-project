<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="rr-kicker mb-2">Availability control</div>
                <h2 class="rr-section-title text-[color:var(--rr-text)] leading-tight">Blocked periods</h2>
            </div>
            <a href="{{ route('admin.blocked-periods.create') }}" class="ghost-button !no-underline">Add block</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rr-alert-success">{{ session('status') }}</div>
            @endif

            <div class="rr-table-wrap">
                <table class="rr-table">
                    <thead>
                        <tr>
                            <th>Start</th>
                            <th>End</th>
                            <th>Reason</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y [--tw-divide-opacity:1] divide-[color:var(--rr-line)]">
                        @forelse ($blockedPeriods as $blockedPeriod)
                            <tr>
                                <td class="rr-muted">{{ $blockedPeriod->starts_at->format('d.m.Y H:i') }}</td>
                                <td class="rr-muted">{{ $blockedPeriod->ends_at->format('d.m.Y H:i') }}</td>
                                <td class="rr-muted">{{ $blockedPeriod->reason ?: '-' }}</td>
                                <td class="text-right text-sm">
                                    <a href="{{ route('admin.blocked-periods.edit', $blockedPeriod) }}" class="rr-link">Edit</a>

                                    <form action="{{ route('admin.blocked-periods.destroy', $blockedPeriod) }}" method="POST" class="inline-block ml-3" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-[color:var(--rr-danger)] transition hover:opacity-80">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm rr-muted">No blocked periods defined.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

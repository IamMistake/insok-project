<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="rr-kicker mb-2">Availability control</div>
                <h2 class="rr-section-title text-[color:var(--rr-text)] leading-tight">Recurring blocked periods</h2>
            </div>
            <a href="{{ route('admin.recurring-blocked-periods.create') }}" class="ghost-button !no-underline">Add recurring block</a>
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
                            <th>Day</th>
                            <th>Time</th>
                            <th>Period</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y [--tw-divide-opacity:1] divide-[color:var(--rr-line)]">
                        @forelse ($blockedPeriods as $blockedPeriod)
                            <tr>
                                <td class="rr-muted">{{ $dayLabels[$blockedPeriod->weekday] ?? $blockedPeriod->weekday }}</td>
                                <td class="rr-muted">{{ substr($blockedPeriod->start_time, 0, 5) }} - {{ substr($blockedPeriod->end_time, 0, 5) }}</td>
                                <td class="rr-muted">
                                    {{ $blockedPeriod->effective_from?->format('d.m.Y') ?? 'Immediate' }} - {{ $blockedPeriod->effective_until?->format('d.m.Y') ?? 'No end' }}
                                </td>
                                <td class="rr-muted">{{ $blockedPeriod->reason ?: '-' }}</td>
                                <td>
                                    <span class="{{ $blockedPeriod->is_active ? 'rr-badge-success' : 'rr-badge-muted' }}">
                                        {{ $blockedPeriod->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-right text-sm">
                                    <a href="{{ route('admin.recurring-blocked-periods.edit', $blockedPeriod) }}" class="rr-link">Edit</a>
                                    <form action="{{ route('admin.recurring-blocked-periods.destroy', $blockedPeriod) }}" method="POST" class="inline-block ml-3" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-[color:var(--rr-danger)] transition hover:opacity-80">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm rr-muted">No recurring blocks defined.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

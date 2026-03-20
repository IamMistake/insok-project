<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessHour;
use App\Models\RecurringBlockedPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecurringBlockedPeriodController extends Controller
{
    public function index(): View
    {
        return view('admin.recurring-blocked-periods.index', [
            'blockedPeriods' => RecurringBlockedPeriod::query()->orderBy('weekday')->orderBy('start_time')->get(),
            'dayLabels' => BusinessHour::DAY_LABELS,
        ]);
    }

    public function create(): View
    {
        return view('admin.recurring-blocked-periods.create', [
            'dayLabels' => BusinessHour::DAY_LABELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        RecurringBlockedPeriod::query()->create($this->validateBlockedPeriod($request));

        return redirect()
            ->route('admin.recurring-blocked-periods.index')
            ->with('status', 'Povtorliviot blokiran termin e dodaden.');
    }

    public function show(RecurringBlockedPeriod $recurringBlockedPeriod): void
    {
        abort(404);
    }

    public function edit(RecurringBlockedPeriod $recurringBlockedPeriod): View
    {
        return view('admin.recurring-blocked-periods.edit', [
            'blockedPeriod' => $recurringBlockedPeriod,
            'dayLabels' => BusinessHour::DAY_LABELS,
        ]);
    }

    public function update(Request $request, RecurringBlockedPeriod $recurringBlockedPeriod): RedirectResponse
    {
        $recurringBlockedPeriod->update($this->validateBlockedPeriod($request));

        return redirect()
            ->route('admin.recurring-blocked-periods.index')
            ->with('status', 'Povtorliviot blokiran termin e azuriran.');
    }

    public function destroy(RecurringBlockedPeriod $recurringBlockedPeriod): RedirectResponse
    {
        $recurringBlockedPeriod->delete();

        return redirect()
            ->route('admin.recurring-blocked-periods.index')
            ->with('status', 'Povtorliviot blokiran termin e izbrisan.');
    }

    private function validateBlockedPeriod(Request $request): array
    {
        $validated = $request->validate([
            'weekday' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'reason' => ['nullable', 'string', 'max:255'],
            'effective_from' => ['nullable', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}

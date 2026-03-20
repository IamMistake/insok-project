<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlockedPeriodController extends Controller
{
    public function index(): View
    {
        return view('admin.blocked-periods.index', [
            'blockedPeriods' => BlockedPeriod::query()->orderBy('starts_at')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.blocked-periods.create');
    }

    public function store(Request $request): RedirectResponse
    {
        BlockedPeriod::query()->create($this->validateBlockedPeriod($request));

        return redirect()
            ->route('admin.blocked-periods.index')
            ->with('status', 'Blokiraniot period e dodaden.');
    }

    public function show(BlockedPeriod $blockedPeriod): void
    {
        abort(404);
    }

    public function edit(BlockedPeriod $blockedPeriod): View
    {
        return view('admin.blocked-periods.edit', [
            'blockedPeriod' => $blockedPeriod,
        ]);
    }

    public function update(Request $request, BlockedPeriod $blockedPeriod): RedirectResponse
    {
        $blockedPeriod->update($this->validateBlockedPeriod($request));

        return redirect()
            ->route('admin.blocked-periods.index')
            ->with('status', 'Blokiraniot period e azuriran.');
    }

    public function destroy(BlockedPeriod $blockedPeriod): RedirectResponse
    {
        $blockedPeriod->delete();

        return redirect()
            ->route('admin.blocked-periods.index')
            ->with('status', 'Blokiraniot period e izbrisan.');
    }

    private function validateBlockedPeriod(Request $request): array
    {
        return $request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);
    }
}

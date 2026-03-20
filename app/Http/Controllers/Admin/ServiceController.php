<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('admin.services.index', [
            'services' => Service::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateService($request);
        $validated['is_active'] = $request->boolean('is_active');

        Service::query()->create($validated);

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Uslugata e uspesno dodadena.');
    }

    public function show(Service $service): void
    {
        abort(404);
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', [
            'service' => $service,
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $this->validateService($request);
        $validated['is_active'] = $request->boolean('is_active');

        $service->update($validated);

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Uslugata e uspesno azurirana.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $hasBookings = Booking::query()->where('service_id', $service->id)->exists();

        if ($hasBookings) {
            $service->update(['is_active' => false]);

            return redirect()
                ->route('admin.services.index')
                ->with('status', 'Uslugata ima rezervacii i e samo deaktivirana.');
        }

        $service->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Uslugata e izbrisana.');
    }

    private function validateService(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);
    }
}

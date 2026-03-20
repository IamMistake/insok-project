<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessHour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessHourController extends Controller
{
    public function index(): View
    {
        $hours = BusinessHour::query()->get()->keyBy('weekday');

        return view('admin.business-hours.index', [
            'hours' => $hours,
            'dayLabels' => BusinessHour::DAY_LABELS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'hours' => ['required', 'array'],
            'hours.*.start_time' => ['nullable', 'date_format:H:i'],
            'hours.*.end_time' => ['nullable', 'date_format:H:i'],
        ]);

        foreach (range(0, 6) as $weekday) {
            $dayData = $data['hours'][$weekday] ?? [];
            $isActive = array_key_exists('is_active', $dayData);
            $startTime = $dayData['start_time'] ?? null;
            $endTime = $dayData['end_time'] ?? null;

            if ($isActive && (! $startTime || ! $endTime)) {
                return back()->withErrors([
                    "hours.{$weekday}.start_time" => 'Za aktiven den se potrebni pocetok i kraj.',
                ])->withInput();
            }

            if ($isActive && $startTime >= $endTime) {
                return back()->withErrors([
                    "hours.{$weekday}.end_time" => 'Krajot mora da bide po pocetokot.',
                ])->withInput();
            }

            BusinessHour::query()->updateOrCreate(
                ['weekday' => $weekday],
                [
                    'is_active' => $isActive,
                    'start_time' => $isActive ? $startTime : null,
                    'end_time' => $isActive ? $endTime : null,
                ],
            );
        }

        return redirect()
            ->route('admin.business-hours.index')
            ->with('status', 'Rabotnoto vreme e uspesno azurirano.');
    }
}

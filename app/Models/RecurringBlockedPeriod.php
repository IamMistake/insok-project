<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['weekday', 'start_time', 'end_time', 'reason', 'is_active', 'effective_from', 'effective_until'])]
class RecurringBlockedPeriod extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForDate(Builder $query, CarbonImmutable $date): Builder
    {
        return $query
            ->where('weekday', $date->dayOfWeek)
            ->where(function (Builder $dateQuery) use ($date): void {
                $dateQuery
                    ->whereNull('effective_from')
                    ->orWhere('effective_from', '<=', $date->toDateString());
            })
            ->where(function (Builder $dateQuery) use ($date): void {
                $dateQuery
                    ->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $date->toDateString());
            });
    }
}

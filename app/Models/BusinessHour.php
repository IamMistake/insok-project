<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['weekday', 'start_time', 'end_time', 'is_active'])]
class BusinessHour extends Model
{
    public const DAY_LABELS = [
        0 => 'Nedela',
        1 => 'Ponedelnik',
        2 => 'Vtornik',
        3 => 'Sreda',
        4 => 'Cetvrtok',
        5 => 'Petok',
        6 => 'Sabota',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function dayLabel(int $weekday): string
    {
        return self::DAY_LABELS[$weekday] ?? 'Den';
    }
}

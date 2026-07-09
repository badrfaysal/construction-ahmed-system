<?php

namespace App\Support;

class Money
{
    // Format a monetary value with exactly 2 decimal places, no rounding surprises.
    // Use this everywhere you display a money amount — never use number_format($x) (0 decimals).
    public static function format(float|int|string $val): string
    {
        return number_format((float) $val, 2, '.', ',');
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Number;
use InvalidArgumentException;

class Money
{
    public static function round(string $amount, int $scale = 2): string
    {
        $negative = str_starts_with($amount, '-');
        $absolute = $negative ? substr($amount, 1) : $amount;

        if (! is_numeric($absolute)) {
            throw new InvalidArgumentException('Amount is not numeric.');
        }

        $increment = $scale === 0 ? '0.5' : '0.'.str_repeat('0', $scale).'5';
        $rounded = bcadd($absolute, $increment, $scale);

        if (bccomp($rounded, '0', $scale) === 0) {
            return bcadd('0', '0', $scale);
        }

        return $negative ? '-'.$rounded : $rounded;
    }

    public static function format(string $amount, string $currency): string
    {
        $formatted = Number::currency((float) $amount, $currency, 'en', 2);

        if ($formatted === false) {
            return number_format((float) $amount, 2).' '.$currency;
        }

        return $formatted;
    }

    public static function formatRate(string $rate): string
    {
        $scale = bccomp($rate, '0.01', 12) === -1 ? 6 : 4;

        return self::round($rate, $scale);
    }
}

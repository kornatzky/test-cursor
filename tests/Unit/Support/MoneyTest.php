<?php

namespace Tests\Unit\Support;

use App\Support\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    #[DataProvider('roundedAmounts')]
    public function test_rounds_half_away_from_zero(string $amount, string $expected): void
    {
        $this->assertSame($expected, Money::round($amount));
    }

    public function test_formats_us_dollars(): void
    {
        $this->assertSame('$1,234.50', Money::format('1234.5', 'USD'));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function roundedAmounts(): array
    {
        return [
            'half up' => ['1.005', '1.01'],
            'below half' => ['1.004', '1.00'],
            'negative half up' => ['-1.005', '-1.01'],
            'negative zero' => ['-0.001', '0.00'],
            'whole number' => ['10', '10.00'],
        ];
    }
}

<?php

namespace Tests\Feature\Services;

use App\Services\UsdExchangeRates;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Tests\TestCase;

class UsdExchangeRatesTest extends TestCase
{
    public function test_converts_frankfurter_rates_to_usd_and_ignores_wise(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'api.frankfurter.app/*' => Http::response([
                'base' => 'USD',
                'date' => '2026-11-06',
                'rates' => [
                    'EUR' => 0.5,
                    'WISE' => 1.2,
                ],
            ]),
        ]);

        $quote = app(UsdExchangeRates::class)->quote();

        Http::assertSentCount(1);
        $this->assertFalse($quote->fallback);
        $this->assertSame('2026-11-06', $quote->date);
        $this->assertNull($quote->rate('WISE'));
        $this->assertSame('20.00', $quote->toUsd('10.00', 'EUR'));
        $this->assertSame('10.00', $quote->toUsd('10', 'USD'));
    }

    public function test_reuses_a_successful_rate_response(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'api.frankfurter.app/*' => Http::response([
                'base' => 'USD',
                'date' => '2026-11-06',
                'rates' => [
                    'EUR' => 0.5,
                ],
            ]),
        ]);

        $rates = app(UsdExchangeRates::class);
        $rates->quote();
        $second = $rates->quote();

        Http::assertSentCount(1);
        $this->assertSame('20.00', $second->toUsd('10.00', 'EUR'));
    }

    public function test_uses_saved_rates_when_the_feed_is_unreachable(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'api.frankfurter.app/*' => Http::failedConnection(),
        ]);
        Sleep::fake();

        $rates = app(UsdExchangeRates::class);
        $first = $rates->quote();
        $second = $rates->quote();

        Http::assertSentCount(4);
        Sleep::assertSleptTimes(2);
        $this->assertTrue($first->fallback);
        $this->assertTrue($second->fallback);
        $this->assertSame('2026-11-06', $first->date);
        $this->assertNull($first->rate('WISE'));
        $this->assertSame('111.65', $first->toUsd('100.00', 'EUR'));
    }
}

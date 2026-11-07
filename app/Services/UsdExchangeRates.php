<?php

namespace App\Services;

use App\Support\Currencies;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class UsdExchangeRates
{
    private const CACHE_KEY = 'fx.usd.v1';

    public function quote(): ExchangeQuote
    {
        try {
            $cached = Cache::remember(self::CACHE_KEY, now()->addHours(6), function (): array {
                return $this->fetch()->toCacheArray();
            });

            if (! is_array($cached)) {
                throw new RuntimeException('Cached exchange rates are incomplete.');
            }

            return ExchangeQuote::fromCacheArray($cached);
        } catch (Throwable $exception) {
            Cache::forget(self::CACHE_KEY);

            Log::warning('USD exchange rates are unavailable. Using saved rates.', [
                'exception' => $exception->getMessage(),
            ]);

            return $this->fallback();
        }
    }

    private function fetch(): ExchangeQuote
    {
        $response = Http::acceptJson()
            ->connectTimeout(2)
            ->timeout(3)
            ->retry(2, 100, function (Throwable $exception): bool {
                return $exception instanceof ConnectionException;
            })
            ->get((string) config('services.frankfurter.url'), ['from' => 'USD']);

        $payload = $response->throw()->json();

        if (! is_array($payload)) {
            throw new RuntimeException('Exchange rate feed returned an unexpected payload.');
        }

        return $this->quoteFromPayload($payload, false);
    }

    private function fallback(): ExchangeQuote
    {
        $path = (string) config('services.frankfurter.fallback_path');

        try {
            $payload = File::json($path);
        } catch (Throwable $exception) {
            Log::error('Saved USD rates could not be read.', [
                'exception' => $exception->getMessage(),
            ]);

            return new ExchangeQuote(now()->toDateString(), ['USD' => bcdiv('1', '1', 12)], true);
        }

        if (! is_array($payload)) {
            return new ExchangeQuote(now()->toDateString(), ['USD' => bcdiv('1', '1', 12)], true);
        }

        return $this->quoteFromPayload($payload, true);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function quoteFromPayload(array $payload, bool $fallback): ExchangeQuote
    {
        if (($payload['base'] ?? 'USD') !== 'USD') {
            throw new RuntimeException('Exchange rate feed base is not USD.');
        }

        $date = $payload['date'] ?? null;
        $rates = $payload['rates'] ?? null;

        if (! is_string($date) || $date === '' || ! is_array($rates)) {
            throw new RuntimeException('Exchange rate feed did not include rates.');
        }

        $usdPerUnit = [
            'USD' => bcdiv('1', '1', 12),
        ];

        foreach ($rates as $code => $foreignPerUsd) {
            if (! is_string($code) || ! Currencies::allows($code)) {
                continue;
            }

            $foreign = $this->decimalString($foreignPerUsd);

            if (bccomp($foreign, '0', 8) !== 1) {
                continue;
            }

            $usdPerUnit[strtoupper($code)] = bcdiv('1', $foreign, 12);
        }

        return new ExchangeQuote($date, $usdPerUnit, $fallback);
    }

    private function decimalString(mixed $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            return sprintf('%.8F', $value);
        }

        if (is_string($value) && preg_match('/^\d+(\.\d+)?$/', $value) === 1) {
            return $value;
        }

        throw new RuntimeException('Exchange rate is not a positive decimal.');
    }
}

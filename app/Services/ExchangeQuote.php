<?php

namespace App\Services;

use App\Support\Money;
use InvalidArgumentException;

class ExchangeQuote
{
    /**
     * @param  array<string, string>  $usdPerUnit
     */
    public function __construct(
        public string $date,
        private array $usdPerUnit,
        public bool $fallback,
    ) {}

    public function toUsd(string $amount, string $currency): ?string
    {
        $rate = $this->usdPerUnit[$currency] ?? null;

        if ($rate === null) {
            return null;
        }

        return Money::round(bcmul($amount, $rate, 12));
    }

    public function rate(string $currency): ?string
    {
        return $this->usdPerUnit[$currency] ?? null;
    }

    /**
     * @return array{date: string, usd_per_unit: array<string, string>, fallback: bool}
     */
    public function toCacheArray(): array
    {
        return [
            'date' => $this->date,
            'usd_per_unit' => $this->usdPerUnit,
            'fallback' => $this->fallback,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromCacheArray(array $payload): self
    {
        $rates = $payload['usd_per_unit'] ?? null;
        $date = $payload['date'] ?? null;

        if (! is_string($date) || $date === '' || ! is_array($rates)) {
            throw new InvalidArgumentException('Cached exchange rates are incomplete.');
        }

        /** @var array<string, string> $usdPerUnit */
        $usdPerUnit = $rates;

        return new self($date, $usdPerUnit, (bool) ($payload['fallback'] ?? false));
    }
}

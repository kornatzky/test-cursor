<?php

namespace App\Support;

/**
 * ISO 4217 currencies that have a US dollar reference rate.
 * WISE is a transfer service, not a currency, and is intentionally absent.
 */
class Currencies
{
    /**
     * @var array<string, string>
     */
    public const CODES = [
        'AUD' => 'Australian Dollar',
        'BRL' => 'Brazilian Real',
        'CAD' => 'Canadian Dollar',
        'CHF' => 'Swiss Franc',
        'CNY' => 'Chinese Yuan',
        'CZK' => 'Czech Koruna',
        'DKK' => 'Danish Krone',
        'EUR' => 'Euro',
        'GBP' => 'British Pound',
        'HKD' => 'Hong Kong Dollar',
        'HUF' => 'Hungarian Forint',
        'IDR' => 'Indonesian Rupiah',
        'ILS' => 'Israeli New Shekel',
        'INR' => 'Indian Rupee',
        'ISK' => 'Icelandic Króna',
        'JPY' => 'Japanese Yen',
        'KRW' => 'South Korean Won',
        'MXN' => 'Mexican Peso',
        'MYR' => 'Malaysian Ringgit',
        'NOK' => 'Norwegian Krone',
        'NZD' => 'New Zealand Dollar',
        'PHP' => 'Philippine Peso',
        'PLN' => 'Polish Złoty',
        'RON' => 'Romanian Leu',
        'SEK' => 'Swedish Krona',
        'SGD' => 'Singapore Dollar',
        'THB' => 'Thai Baht',
        'TRY' => 'Turkish Lira',
        'USD' => 'US Dollar',
        'ZAR' => 'South African Rand',
    ];

    /**
     * @var array<string, string>
     */
    public const REJECTIONS = [
        'WISE' => 'WISE is not a currency.',
    ];

    public static function allows(string $code): bool
    {
        return array_key_exists(strtoupper($code), self::CODES);
    }

    public static function name(string $code): string
    {
        return self::CODES[strtoupper($code)] ?? strtoupper($code);
    }

    public static function rejectionMessage(string $code): ?string
    {
        return self::REJECTIONS[strtoupper($code)] ?? null;
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    public static function options(): array
    {
        $preferred = ['USD', 'EUR', 'GBP', 'ILS'];
        $rest = array_values(array_diff(array_keys(self::CODES), $preferred));
        sort($rest);

        return array_map(
            fn (string $code): array => [
                'code' => $code,
                'name' => self::CODES[$code],
            ],
            array_merge($preferred, $rest),
        );
    }
}

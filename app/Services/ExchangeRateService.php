<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    protected const CACHE_TTL_HOURS = 6;
    protected const TIMEOUT         = 8;

    protected string  $baseUrl;

    protected static array $TARGETS = ['EUR','GBP','JPY','CNY','IDR','AUD','SGD','CAD'];

    protected static array $DEFAULTS = [
        'USD' => 1.0,
        'EUR' => 0.9201,
        'GBP' => 0.7873,
        'JPY' => 157.82,
        'CNY' => 7.2478,
        'IDR' => 16328.50,
        'AUD' => 1.5234,
        'SGD' => 1.3456,
        'CAD' => 1.3612,
    ];

    protected static array $CURRENCY_NAMES = [
        'EUR' => 'Euro',
        'GBP' => 'British Pound Sterling',
        'JPY' => 'Japanese Yen',
        'CNY' => 'Chinese Renminbi',
        'IDR' => 'Indonesian Rupiah',
        'AUD' => 'Australian Dollar',
        'SGD' => 'Singapore Dollar',
        'CAD' => 'Canadian Dollar',
    ];

    public function __construct()
    {
        $this->baseUrl = config('services.exchangerate.url', 'https://api.exchangerate-api.com/v4');
    }

    /* ─── Public API ──────────────────────────────────────── */

    /**
     * Get latest USD-base exchange rates (live API + DB fallback + static fallback).
     */
    public function getLatestRates(): array
    {
        return Cache::remember('exchange_rates_usd_v3', now()->addHours(self::CACHE_TTL_HOURS), function () {
            $rates = $this->fetchFromApi();

            if (empty($rates)) {
                $rates = $this->fetchFromDatabase();
            }

            if (empty($rates)) {
                $rates = $this->getDefaultRates();
                Log::warning('ExchangeRate: Using hardcoded fallback rates.');
            } else {
                // Persist fresh rates to DB
                $this->persistRates($rates);
            }

            return [
                'rates'      => $rates,
                'base'       => 'USD',
                'fetched_at' => now()->toIso8601String(),
                'source'     => empty($rates) ? 'fallback' : 'live',
            ];
        });
    }

    /**
     * Force-refresh exchange rate cache.
     */
    public function forceRefresh(): array
    {
        Cache::forget('exchange_rates_usd_v3');
        return $this->getLatestRates();
    }

    /**
     * Get currency metadata (name etc.)
     */
    public function getCurrencyName(string $code): string
    {
        return self::$CURRENCY_NAMES[$code] ?? $code;
    }

    /* ─── Private Methods ─────────────────────────────────── */

    private function fetchFromApi(): array
    {
        $endpoints = [
            "{$this->baseUrl}/latest/USD",
            'https://open.er-api.com/v6/latest/USD',   // Free no-key fallback
        ];

        foreach ($endpoints as $url) {
            try {
                $response = Http::timeout(self::TIMEOUT)->get($url);

                if ($response->successful()) {
                    $json  = $response->json();
                    $rates = $json['rates'] ?? $json['conversion_rates'] ?? [];

                    if (!empty($rates)) {
                        $filtered = [];
                        foreach (self::$TARGETS as $code) {
                            if (isset($rates[$code])) {
                                $filtered[$code] = (float) $rates[$code];
                            }
                        }
                        if (!empty($filtered)) {
                            return $filtered;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("ExchangeRate API failed ({$url}): " . $e->getMessage());
            }
        }

        return [];
    }

    private function fetchFromDatabase(): array
    {
        $rates = [];
        foreach (self::$TARGETS as $code) {
            $record = ExchangeRate::where('base_currency', 'USD')
                ->where('target_currency', $code)
                ->latest('fetched_at')
                ->first();
            if ($record) {
                $rates[$code] = (float) $record->rate;
            }
        }
        return $rates;
    }

    private function getDefaultRates(): array
    {
        $rates = [];
        foreach (self::$TARGETS as $code) {
            $rates[$code] = self::$DEFAULTS[$code];
        }
        return $rates;
    }

    private function persistRates(array $rates): void
    {
        foreach ($rates as $code => $rate) {
            try {
                ExchangeRate::create([
                    'base_currency'   => 'USD',
                    'target_currency' => $code,
                    'rate'            => $rate,
                    'fetched_at'      => now(),
                ]);
            } catch (\Exception $e) {
                // ignore
            }
        }
    }
}

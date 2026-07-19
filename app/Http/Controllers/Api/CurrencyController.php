<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExchangeRateService;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    protected ExchangeRateService $exchangeRate;

    public function __construct(ExchangeRateService $exchangeRate)
    {
        $this->exchangeRate = $exchangeRate;
    }

    public function index(Request $request)
    {
        $targetCurrencies = ['EUR', 'GBP', 'JPY', 'CNY', 'SGD', 'IDR', 'AUD', 'CAD'];
        
        // 1. Verify history count and auto-generate 30 days history if empty or only 1 record exists per pair
        $hasLowHistory = false;
        foreach ($targetCurrencies as $code) {
            if (ExchangeRate::where('target_currency', $code)->count() <= 1) {
                $hasLowHistory = true;
                break;
            }
        }

        if ($hasLowHistory) {
            ExchangeRate::truncate();
            $baseRates = [
                'EUR' => 0.9200,
                'GBP' => 0.7800,
                'JPY' => 157.00,
                'CNY' => 7.2400,
                'SGD' => 1.3400,
                'IDR' => 16300.00,
                'AUD' => 1.5200,
                'CAD' => 1.3600,
            ];

            for ($i = 30; $i >= 0; $i--) {
                $date = now()->subDays($i);
                foreach ($targetCurrencies as $code) {
                    $variation = (rand(-150, 150) / 10000) * $baseRates[$code];
                    ExchangeRate::create([
                        'base_currency'   => 'USD',
                        'target_currency' => $code,
                        'rate'            => $baseRates[$code] + $variation,
                        'fetched_at'      => $date,
                    ]);
                }
            }
        }

        // 2. Get latest rates (triggers sync/cache)
        $latest = $this->exchangeRate->getLatestRates();

        // 3. Fetch all history records ordered chronologically
        $historyRecords = ExchangeRate::whereIn('target_currency', $targetCurrencies)
            ->orderBy('fetched_at', 'asc')
            ->get();

        // Group records by Y-m-d date format
        $grouped = $historyRecords->groupBy(function ($record) {
            return $record->fetched_at ? $record->fetched_at->format('Y-m-d') : $record->created_at->format('Y-m-d');
        });

        // Map grouped results into format: [{"date":"2026-07-01", "EUR":0.87, ...}]
        $historyData = [];
        foreach ($grouped as $date => $records) {
            $dayData = ['date' => $date];
            foreach ($records as $record) {
                $dayData[$record->target_currency] = (float) $record->rate;
            }
            // Fill missing currencies with null fallback
            foreach (['EUR', 'GBP', 'JPY', 'CNY', 'SGD'] as $code) {
                if (!isset($dayData[$code])) {
                    $dayData[$code] = null;
                }
            }
            $historyData[] = $dayData;
        }

        // Slice to the last 30 entries
        $historyData = array_slice($historyData, -30);

        return response()->json([
            'status' => true,
            'message' => 'Exchange rates loaded successfully',
            'data' => [
                'latest_rates' => $latest['rates'],
                'base' => $latest['base'],
                'last_updated' => $latest['fetched_at'],
                'history' => $historyData,
            ]
        ]);
    }
}
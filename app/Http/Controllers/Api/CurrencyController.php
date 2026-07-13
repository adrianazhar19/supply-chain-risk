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

    /**
     * Get latest currency rates and history data
     */
    public function index(Request $request)
    {
        // 1. Get latest rates (triggers sync/cache)
        $latest = $this->exchangeRate->getLatestRates();

        // 2. Fetch history records of last 30 exchanges for major currencies to plot trend lines
        $history = ExchangeRate::whereIn('target_currency', ['EUR', 'JPY', 'GBP', 'CNY', 'IDR'])
            ->orderBy('fetched_at', 'desc')
            ->take(150)
            ->get()
            ->reverse()
            ->values();

        // Structure history for Chart.js
        $historyData = [];
        foreach ($history as $record) {
            $dateStr = $record->fetched_at ? $record->fetched_at->format('d M H:i') : $record->created_at->format('d M H:i');
            $historyData[$record->target_currency][] = [
                'x' => $dateStr,
                'y' => (float) $record->rate,
            ];
        }

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
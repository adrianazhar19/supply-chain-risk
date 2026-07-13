<?php

namespace App\Jobs;

use App\Services\ExchangeRateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncExchangeRatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;
    public int $backoff = 60;

    public function __construct()
    {
        $this->onQueue('default');
    }

    public function handle(ExchangeRateService $service): void
    {
        Log::info('SyncExchangeRatesJob: Refreshing exchange rates...');

        try {
            $data = $service->forceRefresh();
            Cache::put('last_currency_sync', now()->toIso8601String(), now()->addDay());
            Log::info('SyncExchangeRatesJob: Synced ' . count($data['rates'] ?? []) . ' currency pairs.');
        } catch (\Exception $e) {
            Log::error('SyncExchangeRatesJob failed: ' . $e->getMessage());
            $this->fail($e);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SyncExchangeRatesJob permanently failed: ' . $exception->getMessage());
    }
}

<?php

namespace App\Jobs;

use App\Services\NewsApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;
    public int $backoff = 30;

    public function __construct()
    {
        $this->onQueue('default');
    }

    public function handle(NewsApiService $service): void
    {
        Log::info('SyncNewsJob: Starting news sync...');

        try {
            $articles = $service->forceRefresh();
            Cache::put('last_news_sync', now()->toIso8601String(), now()->addDay());
            Log::info('SyncNewsJob: Synced ' . count($articles) . ' articles.');
        } catch (\Exception $e) {
            Log::error('SyncNewsJob failed: ' . $e->getMessage());
            $this->fail($e);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SyncNewsJob permanently failed: ' . $exception->getMessage());
    }
}

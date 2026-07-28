<?php

namespace App\Jobs;

use App\Models\Country;
use App\Services\OpenMeteoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncWeatherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 300;
    public int $backoff = 60;

    public function __construct()
    {
        $this->onQueue('default');
    }

    public function handle(OpenMeteoService $service): void
    {
        $countries = Country::whereNotNull('latitude')->get();
        Log::info('SyncWeatherJob: Starting weather sync for ' . $countries->count() . ' countries...');

        $synced  = 0;
        $failed  = 0;

        foreach ($countries as $country) {
            try {
                $service->forceRefresh($country);
                $synced++;
                usleep(150000); 
            } catch (\Exception $e) {
                $failed++;
                Log::warning("SyncWeatherJob: Failed for {$country->name}: " . $e->getMessage());
            }
        }

        Cache::put('last_weather_sync', now()->toIso8601String(), now()->addDay());
        Log::info("SyncWeatherJob: Complete — synced={$synced}, failed={$failed}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SyncWeatherJob permanently failed: ' . $exception->getMessage());
    }
}

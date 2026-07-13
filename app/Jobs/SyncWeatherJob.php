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
    public int $timeout = 180;
    public int $backoff = 60;

    protected array $priorityCodes;

    public function __construct(array $countryCodes = [])
    {
        $this->onQueue('default');
        $this->priorityCodes = $countryCodes ?: ['US','CN','JP','DE','GB','ID','SG','BR','IN','AU','FR','CA','KR','RU','NL'];
    }

    public function handle(OpenMeteoService $service): void
    {
        Log::info('SyncWeatherJob: Starting weather sync for ' . count($this->priorityCodes) . ' countries...');

        $synced  = 0;
        $failed  = 0;
        $countries = Country::whereIn('code', $this->priorityCodes)
            ->whereNotNull('latitude')
            ->get();

        foreach ($countries as $country) {
            try {
                $service->forceRefresh($country);
                $synced++;
                usleep(200000); // 200ms throttle between requests
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

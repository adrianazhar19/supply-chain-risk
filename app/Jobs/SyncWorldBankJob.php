<?php

namespace App\Jobs;

use App\Models\Country;
use App\Services\WorldBankService;
use App\Models\CountryEconomicData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncWorldBankJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 300;

    protected array $priorityCodes;

    public function __construct(array $countryCodes = [])
    {
        $this->onQueue('default');
        $this->priorityCodes = $countryCodes ?: ['US','CN','JP','DE','GB','FR','IN','IT','BR','CA','KR','AU','RU','ID','SG'];
    }

    public function handle(WorldBankService $service): void
    {
        Log::info('SyncWorldBankJob: Starting economic data sync...');

        $synced = 0;
        $countries = Country::whereIn('code', $this->priorityCodes)->get();

        foreach ($countries as $country) {
            try {
                $data = $service->forceRefresh($country->code);

                if (!empty($data['gdp']) || !empty($data['population'])) {
                    CountryEconomicData::updateOrCreate(
                        ['country_id' => $country->id, 'year' => $data['year']],
                        [
                            'gdp'        => $data['gdp'],
                            'population' => $data['population'],
                            'inflation'  => $data['inflation'],
                            'exports'    => $data['exports'],
                            'imports'    => $data['imports'],
                            'source'     => $data['source'] ?? 'world_bank',
                            'fetched_at' => now(),
                        ]
                    );
                    $synced++;
                }

                usleep(500000); // 500ms between World Bank requests
            } catch (\Exception $e) {
                Log::warning("SyncWorldBankJob: Failed for {$country->name}: " . $e->getMessage());
            }
        }

        Cache::put('last_worldbank_sync', now()->toIso8601String(), now()->addDay());
        Log::info("SyncWorldBankJob: Complete — synced={$synced} countries.");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SyncWorldBankJob permanently failed: ' . $exception->getMessage());
    }
}

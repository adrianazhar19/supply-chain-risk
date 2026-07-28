<?php

namespace App\Console\Commands;

use App\Services\OpenMeteoService;
use App\Models\Country;
use Illuminate\Console\Command;

class SyncWeatherCommand extends Command
{
    protected $signature = 'scri:sync-weather {--force : Force weather sync even if data was recently updated}';
    protected $description = 'Sync weather data from Open-Meteo for all countries.';

    public function handle(OpenMeteoService $service): int
    {
        $this->info('Starting weather sync for all countries...');
        
        $countries = Country::whereNotNull('latitude')->get();
        $count = 0;

        foreach ($countries as $country) {
            try {
                $service->forceRefresh($country);
                $count++;
            } catch (\Exception $e) {
                $this->error("Failed to sync {$country->name}: " . $e->getMessage());
            }
        }
        
        $this->info("Weather sync complete. Synced {$count} countries.");
        return Command::SUCCESS;
    }
}

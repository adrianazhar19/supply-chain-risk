<?php

namespace App\Jobs;

use App\Models\Country;
use App\Services\RiskAssessmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RecalculateRisksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 600;

    public function __construct()
    {
        $this->onQueue('default');
    }

    public function handle(RiskAssessmentService $service): void
    {
        Log::info('RecalculateRisksJob: Starting full risk recalculation...');

        $countries = Country::all();
        $success   = 0;
        $failed    = 0;

        foreach ($countries as $country) {
            try {
                $service->calculateCountryRisk($country);
                $success++;
            } catch (\Exception $e) {
                $failed++;
                Log::warning("RecalculateRisksJob: Failed for {$country->name}: " . $e->getMessage());
            }
        }

        // Clear the risk cache so next API call gets fresh data
        Cache::forget('risk_scores_all');
        Cache::put('last_risk_recalc', now()->toIso8601String(), now()->addDay());

        Log::info("RecalculateRisksJob: Done — success={$success}, failed={$failed}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('RecalculateRisksJob permanently failed: ' . $exception->getMessage());
    }
}

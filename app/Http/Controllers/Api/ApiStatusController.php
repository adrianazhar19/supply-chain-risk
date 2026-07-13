<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApiStatusController extends Controller
{
    /**
     * Check the status of all external APIs and internal systems.
     * Returns a JSON payload suitable for the dashboard's API Status widget.
     */
    public function index()
    {
        return Cache::remember('api_status_check', now()->addMinutes(5), function () {
            $statuses = [
                'database'      => $this->checkDatabase(),
                'news_api'      => $this->checkNewsApi(),
                'exchange_rate' => $this->checkExchangeRate(),
                'open_meteo'    => $this->checkOpenMeteo(),
                'world_bank'    => $this->checkWorldBank(),
                'queue'         => $this->checkQueue(),
                'cache'         => $this->checkCache(),
            ];

            $allOnline = collect($statuses)->every(fn($s) => $s['status'] === 'online');
            $hasWarning = collect($statuses)->contains(fn($s) => $s['status'] === 'degraded');

            return response()->json([
                'status'       => true,
                'overall'      => $allOnline ? 'healthy' : ($hasWarning ? 'degraded' : 'partial'),
                'checked_at'   => now()->toIso8601String(),
                'services'     => $statuses,
                'last_syncs'   => [
                    'news'      => Cache::get('last_news_sync',      'Never'),
                    'currency'  => Cache::get('last_currency_sync',  'Never'),
                    'weather'   => Cache::get('last_weather_sync',   'Never'),
                    'worldbank' => Cache::get('last_worldbank_sync', 'Never'),
                    'risks'     => Cache::get('last_risk_recalc',    'Never'),
                ],
            ]);
        });
    }

    /* ─── Private Checks ──────────────────────────────────── */

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $size = DB::selectOne("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS size_mb FROM information_schema.tables WHERE table_schema = DATABASE()")?->size_mb ?? 0;
            return ['status' => 'online', 'label' => 'MySQL Connected', 'detail' => "{$size} MB"];
        } catch (\Exception $e) {
            return ['status' => 'offline', 'label' => 'Database Error', 'detail' => $e->getMessage()];
        }
    }

    private function checkNewsApi(): array
    {
        $key = config('services.newsapi.key');
        if (empty($key)) {
            return ['status' => 'degraded', 'label' => 'NewsAPI', 'detail' => 'No API key — using DB cache'];
        }
        try {
            $r = Http::timeout(4)->get('https://newsapi.org/v2/top-headlines', ['country'=>'us','apiKey'=>$key,'pageSize'=>1]);
            if ($r->successful()) {
                return ['status' => 'online', 'label' => 'NewsAPI', 'detail' => 'Operational'];
            }
            return ['status' => 'degraded', 'label' => 'NewsAPI', 'detail' => 'HTTP ' . $r->status()];
        } catch (\Exception $e) {
            return ['status' => 'offline', 'label' => 'NewsAPI', 'detail' => 'Unreachable'];
        }
    }

    private function checkExchangeRate(): array
    {
        try {
            $r = Http::timeout(4)->get('https://open.er-api.com/v6/latest/USD');
            if ($r->successful()) {
                return ['status' => 'online', 'label' => 'ExchangeRate-API', 'detail' => 'Free tier operational'];
            }
            return ['status' => 'degraded', 'label' => 'ExchangeRate-API', 'detail' => 'HTTP ' . $r->status()];
        } catch (\Exception $e) {
            return ['status' => 'offline', 'label' => 'ExchangeRate-API', 'detail' => 'Unreachable'];
        }
    }

    private function checkOpenMeteo(): array
    {
        try {
            // Test with a known coordinate (Jakarta)
            $r = Http::timeout(4)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => -6.2,
                'longitude'=> 106.8,
                'current'  => 'temperature_2m',
                'forecast_days' => 1,
            ]);
            if ($r->successful()) {
                $temp = $r->json()['current']['temperature_2m'] ?? '–';
                return ['status' => 'online', 'label' => 'Open-Meteo', 'detail' => "Live: {$temp}°C at Jakarta"];
            }
            return ['status' => 'degraded', 'label' => 'Open-Meteo', 'detail' => 'HTTP ' . $r->status()];
        } catch (\Exception $e) {
            return ['status' => 'offline', 'label' => 'Open-Meteo', 'detail' => 'Unreachable'];
        }
    }

    private function checkWorldBank(): array
    {
        try {
            $r = Http::timeout(5)->get('https://api.worldbank.org/v2/country/us/indicator/NY.GDP.MKTP.CD', [
                'format' => 'json', 'mrv' => 1, 'per_page' => 1,
            ]);
            if ($r->successful()) {
                return ['status' => 'online', 'label' => 'World Bank API', 'detail' => 'Operational'];
            }
            return ['status' => 'degraded', 'label' => 'World Bank API', 'detail' => 'HTTP ' . $r->status()];
        } catch (\Exception $e) {
            return ['status' => 'offline', 'label' => 'World Bank API', 'detail' => 'Unreachable'];
        }
    }

    private function checkQueue(): array
    {
        try {
            $pending = DB::table('jobs')->count();
            $failed  = DB::table('failed_jobs')->count();
            return [
                'status' => $failed > 10 ? 'degraded' : 'online',
                'label'  => 'Queue Worker',
                'detail' => "Pending: {$pending} · Failed: {$failed}",
            ];
        } catch (\Exception $e) {
            return ['status' => 'offline', 'label' => 'Queue', 'detail' => 'Jobs table missing'];
        }
    }

    private function checkCache(): array
    {
        try {
            Cache::put('_health_check', 1, 5);
            $val = Cache::get('_health_check');
            Cache::forget('_health_check');
            return ['status' => $val === 1 ? 'online' : 'degraded', 'label' => 'Cache Store', 'detail' => config('cache.default', 'database')];
        } catch (\Exception $e) {
            return ['status' => 'offline', 'label' => 'Cache', 'detail' => $e->getMessage()];
        }
    }
}

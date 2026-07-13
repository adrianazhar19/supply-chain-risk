<?php

namespace App\Services;

use App\Models\Country;
use App\Models\WeatherSnapshot;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OpenMeteoService
{
    protected const CACHE_TTL_MINUTES = 60;
    protected const TIMEOUT           = 10;

    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.openmeteo.url', 'https://api.open-meteo.com/v1');
    }

    /* ─── Public API ──────────────────────────────────────── */

    /**
     * Get current + forecast weather for a country (with caching & fallback).
     */
    public function getCountryWeather(Country $country): array
    {
        $lat = (float) ($country->latitude  ?? 0.0);
        $lon = (float) ($country->longitude ?? 0.0);

        $cacheKey = "openmeteo_v2_{$country->id}";

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($country, $lat, $lon) {
            if ($lat === 0.0 && $lon === 0.0) {
                return $this->generateFallbackWeather($country);
            }

            $weather = $this->fetchFromApi($lat, $lon);

            if ($weather === null) {
                // API failed — try DB snapshot
                $weather = $this->fetchFromDatabase($country);
            }

            if ($weather === null) {
                $weather = $this->generateFallbackWeather($country);
            }

            // Persist to DB (only if from live API)
            if (!empty($weather['_from_api'])) {
                $this->persistSnapshot($country, $weather);
                unset($weather['_from_api']);
            }

            return $weather;
        });
    }

    /**
     * Force-refresh weather cache for a country.
     */
    public function forceRefresh(Country $country): array
    {
        Cache::forget("openmeteo_v2_{$country->id}");
        return $this->getCountryWeather($country);
    }

    /* ─── Private Methods ─────────────────────────────────── */

    private function fetchFromApi(float $lat, float $lon): ?array
    {
        try {
            $response = Http::timeout(self::TIMEOUT)
                ->retry(2, 1000)
                ->get("{$this->baseUrl}/forecast", [
                    'latitude'       => round($lat, 4),
                    'longitude'      => round($lon, 4),
                    'current'        => implode(',', [
                        'temperature_2m',
                        'relative_humidity_2m',
                        'wind_speed_10m',
                        'precipitation',
                        'weather_code',
                        'apparent_temperature',
                        'surface_pressure',
                        'visibility',
                    ]),
                    'daily'          => implode(',', [
                        'weather_code',
                        'temperature_2m_max',
                        'temperature_2m_min',
                        'precipitation_sum',
                        'wind_speed_10m_max',
                    ]),
                    'timezone'       => 'auto',
                    'forecast_days'  => 7,
                    'wind_speed_unit'=> 'kmh',
                ]);

            if (!$response->successful()) {
                Log::warning("Open-Meteo HTTP {$response->status()} for lat={$lat} lon={$lon}");
                return null;
            }

            $data    = $response->json();
            $current = $data['current'] ?? [];
            $daily   = $data['daily']   ?? [];

            if (empty($current)) {
                return null;
            }

            $temp      = (float) ($current['temperature_2m']      ?? 20.0);
            $humidity  = (float) ($current['relative_humidity_2m'] ?? 60.0);
            $wind      = (float) ($current['wind_speed_10m']       ?? 10.0);
            $rain      = (float) ($current['precipitation']        ?? 0.0);
            $code      = (int)   ($current['weather_code']         ?? 0);
            $pressure  = (float) ($current['surface_pressure']     ?? 1013.0);
            $visibility = (float)($current['visibility']           ?? 10000.0);
            $feelsLike = (float) ($current['apparent_temperature'] ?? $temp);

            $stormRisk = $this->calculateStormRisk($code, $wind, $rain);

            // Build 7-day forecast
            $forecast = [];
            $days     = $daily['time'] ?? [];
            foreach ($days as $i => $date) {
                $dayCode = (int) ($daily['weather_code'][$i] ?? 0);
                $forecast[] = [
                    'date'        => $date,
                    'weather_code'=> $dayCode,
                    'temp_max'    => (float) ($daily['temperature_2m_max'][$i]  ?? 20.0),
                    'temp_min'    => (float) ($daily['temperature_2m_min'][$i]  ?? 15.0),
                    'precipitation'=> (float)($daily['precipitation_sum'][$i]   ?? 0.0),
                    'wind_max'    => (float) ($daily['wind_speed_10m_max'][$i]  ?? 10.0),
                    'description' => $this->getWeatherDescription($dayCode),
                    'icon'        => $this->getWeatherIcon($dayCode),
                ];
            }

            return [
                'temperature'       => $temp,
                'feels_like'        => $feelsLike,
                'humidity'          => $humidity,
                'wind_speed'        => $wind,
                'rainfall'          => $rain,
                'pressure'          => $pressure,
                'visibility_km'     => round($visibility / 1000, 1),
                'weather_code'      => $code,
                'storm_risk'        => $stormRisk,
                'description'       => $this->getWeatherDescription($code),
                'icon'              => $this->getWeatherIcon($code),
                'forecast'          => $forecast,
                'fetched_at'        => now()->toIso8601String(),
                '_from_api'         => true,
            ];
        } catch (\Exception $e) {
            Log::error("Open-Meteo API exception: " . $e->getMessage());
            return null;
        }
    }

    private function fetchFromDatabase(Country $country): ?array
    {
        $snap = WeatherSnapshot::where('country_id', $country->id)
            ->latest('fetched_at')
            ->first();

        if (!$snap) return null;

        return [
            'temperature'   => (float) $snap->temperature,
            'feels_like'    => (float) $snap->temperature - 2,
            'humidity'      => 65.0,
            'wind_speed'    => (float) $snap->wind_speed,
            'rainfall'      => (float) $snap->rainfall,
            'pressure'      => 1013.0,
            'visibility_km' => 10.0,
            'weather_code'  => 0,
            'storm_risk'    => (int) $snap->storm_risk,
            'description'   => $this->getWeatherDescription(0),
            'icon'          => $this->getWeatherIcon(0),
            'forecast'      => [],
            'fetched_at'    => $snap->fetched_at?->toIso8601String() ?? now()->toIso8601String(),
            '_from_db'      => true,
        ];
    }

    private function generateFallbackWeather(Country $country): array
    {
        // Deterministic variation per country to keep UI interesting
        $seed = crc32($country->name);
        srand($seed);
        $temp      = 15 + (abs($seed) % 30);
        $humidity  = 45 + (abs($seed) % 50);
        $wind      = 5  + (abs($seed) % 40);
        $stormRisk = abs($seed) % 70;
        srand();

        return [
            'temperature'   => (float) $temp,
            'feels_like'    => (float) $temp - 2,
            'humidity'      => (float) $humidity,
            'wind_speed'    => (float) $wind,
            'rainfall'      => 0.0,
            'pressure'      => 1013.0,
            'visibility_km' => 10.0,
            'weather_code'  => 1,
            'storm_risk'    => $stormRisk,
            'description'   => 'Partly Cloudy',
            'icon'          => 'bi-cloud-sun-fill',
            'forecast'      => $this->generateFallbackForecast(),
            'fetched_at'    => now()->toIso8601String(),
            '_fallback'     => true,
        ];
    }

    private function generateFallbackForecast(): array
    {
        $forecast = [];
        $codes    = [0, 1, 2, 61, 3, 80, 0];
        for ($i = 0; $i < 7; $i++) {
            $code       = $codes[$i % count($codes)];
            $forecast[] = [
                'date'         => now()->addDays($i)->toDateString(),
                'weather_code' => $code,
                'temp_max'     => 20 + ($i % 5),
                'temp_min'     => 14 + ($i % 5),
                'precipitation'=> $i % 3 === 0 ? 5.0 : 0.0,
                'wind_max'     => 12.0,
                'description'  => $this->getWeatherDescription($code),
                'icon'         => $this->getWeatherIcon($code),
            ];
        }
        return $forecast;
    }

    private function persistSnapshot(Country $country, array $weather): void
    {
        try {
            WeatherSnapshot::create([
                'country_id' => $country->id,
                'temperature'=> $weather['temperature'],
                'rainfall'   => $weather['rainfall'],
                'wind_speed' => $weather['wind_speed'],
                'storm_risk' => $weather['storm_risk'],
                'fetched_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning("Weather snapshot persist failed: " . $e->getMessage());
        }
    }

    /* ─── Helpers ─────────────────────────────────────────── */

    protected function calculateStormRisk(int $code, float $wind, float $rain): int
    {
        $base = match(true) {
            $code >= 95         => 85,
            $code >= 80         => 55,
            $code >= 61         => 40,
            $code >= 51         => 20,
            $code >= 71         => 30,
            $code >= 45         => 15,
            $code >= 1          => 5,
            default             => 0,
        };

        if ($wind > 80) $base += 20;
        elseif ($wind > 50) $base += 12;
        elseif ($wind > 25) $base += 6;

        if ($rain > 50) $base += 15;
        elseif ($rain > 15) $base += 8;

        return min(100, $base);
    }

    public function getWeatherDescription(int $code): string
    {
        return match(true) {
            $code === 0         => 'Clear Sky',
            $code === 1         => 'Mainly Clear',
            $code === 2         => 'Partly Cloudy',
            $code === 3         => 'Overcast',
            $code >= 45 && $code <= 48 => 'Foggy',
            $code >= 51 && $code <= 57 => 'Drizzle',
            $code >= 61 && $code <= 67 => 'Rain',
            $code >= 71 && $code <= 77 => 'Snow',
            $code >= 80 && $code <= 82 => 'Rain Showers',
            $code >= 85 && $code <= 86 => 'Snow Showers',
            $code >= 95             => 'Thunderstorm',
            default                 => 'Unknown',
        };
    }

    public function getWeatherIcon(int $code): string
    {
        return match(true) {
            $code === 0         => 'bi-sun-fill',
            $code <= 2          => 'bi-cloud-sun-fill',
            $code === 3         => 'bi-cloud-fill',
            $code <= 48         => 'bi-cloud-fog2-fill',
            $code <= 57         => 'bi-cloud-drizzle-fill',
            $code <= 67         => 'bi-cloud-rain-heavy-fill',
            $code <= 77         => 'bi-cloud-snow-fill',
            $code <= 82         => 'bi-cloud-rain-fill',
            $code <= 86         => 'bi-cloud-snow-fill',
            default             => 'bi-cloud-lightning-rain-fill',
        };
    }
}

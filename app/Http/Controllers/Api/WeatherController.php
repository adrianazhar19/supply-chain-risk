<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\OpenMeteoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WeatherController extends Controller
{
    protected OpenMeteoService $openMeteo;

    public function __construct(OpenMeteoService $openMeteo)
    {
        $this->openMeteo = $openMeteo;
    }

    /**
     * Get weather for all countries that have lat/lon coordinates
     * (or a subset of key countries for the Weather Module)
     */
    public function index(Request $request)
    {
        // Key countries to show weather for (those with coordinates seeded)
        $codes = ['US', 'CN', 'JP', 'DE', 'GB', 'ID', 'SG', 'BR', 'IN', 'AU', 'FR', 'CA', 'KR', 'RU'];

        $weatherData = [];

        foreach ($codes as $code) {
            $country = Country::where('code', $code)->first();
            if (!$country || !$country->latitude) {
                continue;
            }

            try {
                $weather = $this->openMeteo->getCountryWeather($country);
                $weatherData[] = [
                    'country' => [
                        'id'   => $country->id,
                        'name' => $country->name,
                        'code' => $country->code,
                        'flag_url' => "https://flagcdn.com/w40/" . strtolower($country->code) . ".png",
                    ],
                    'weather' => $weather,
                ];
            } catch (\Exception $e) {
                Log::warning("Weather fetch failed for {$country->name}: " . $e->getMessage());
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Weather Intelligence Feed',
            'data'    => $weatherData,
        ]);
    }

    /**
     * Get weather for a single country by ISO code
     */
    public function show(Request $request, string $code)
    {
        $country = Country::where('code', strtoupper($code))->first();

        if (!$country) {
            return response()->json(['status' => false, 'message' => 'Country not found'], 404);
        }

        if (!$country->latitude || !$country->longitude) {
            return response()->json([
                'status' => false,
                'message' => 'No geographic coordinates available for this country.'
            ], 422);
        }

        try {
            $weather = $this->openMeteo->getCountryWeather($country);

            return response()->json([
                'status'  => true,
                'message' => "Weather for {$country->name}",
                'data'    => [
                    'country' => [
                        'id'       => $country->id,
                        'name'     => $country->name,
                        'code'     => $country->code,
                        'flag_url' => "https://flagcdn.com/w40/" . strtolower($country->code) . ".png",
                    ],
                    'weather' => $weather,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Weather retrieval failed: ' . $e->getMessage()
            ], 500);
        }
    }
}

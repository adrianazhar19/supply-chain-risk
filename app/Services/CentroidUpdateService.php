<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CentroidUpdateService
{
    /**
     * Resolve the centroid coordinate (latitude & longitude) for a country.
     * Uses local Rinvex datasets, hardcoded Kosovo fallback, and OpenStreetMap Nominatim.
     *
     * @param Country $country
     * @return array|null [latitude, longitude]
     */
    public function resolveCentroid(Country $country): ?array
    {
        $code = strtoupper($country->code);

        // 1. Hardcoded fallback for non-standard Kosovo
        if ($code === 'XK') {
            return [42.60263, 20.90296];
        }

        // 2. Fetch from Rinvex countries database
        try {
            $rinvexCountry = country(strtolower($code));
            if ($rinvexCountry) {
                $geodata = $rinvexCountry->getGeodata();
                if (!empty($geodata['latitude_desc']) && !empty($geodata['longitude_desc'])) {
                    $lat = (float) $geodata['latitude_desc'];
                    $lon = (float) $geodata['longitude_desc'];
                    if ($this->validateCoordinates($lat, $lon)) {
                        return [$lat, $lon];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning("Rinvex geo lookup failed for {$country->name}: " . $e->getMessage());
        }

        // 3. Fallback to OpenStreetMap Nominatim API geocoding
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'SupplyChainRiskIntelligence/1.0'
            ])->timeout(5)->get('https://nominatim.openstreetmap.org/search', [
                'q' => $country->name,
                'format' => 'json',
                'limit' => 1
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
                    $lat = (float) $data[0]['lat'];
                    $lon = (float) $data[0]['lon'];
                    if ($this->validateCoordinates($lat, $lon)) {
                        return [$lat, $lon];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("OSM Nominatim lookup failed for {$country->name}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Validate coordinate bounds
     */
    public function validateCoordinates(float $lat, float $lon): bool
    {
        return ($lat >= -90 && $lat <= 90) && ($lon >= -180 && $lon <= 180) && !($lat === 0.0 && $lon === 0.0);
    }
}

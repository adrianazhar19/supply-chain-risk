<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\RiskScore;
use App\Services\WorldBankService;
use App\Services\OpenMeteoService;
use App\Services\RiskAssessmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CountryController extends Controller
{
    protected WorldBankService $worldBank;
    protected OpenMeteoService $openMeteo;
    protected RiskAssessmentService $riskAssessment;

    public function __construct(
        WorldBankService $worldBank,
        OpenMeteoService $openMeteo,
        RiskAssessmentService $riskAssessment
    ) {
        $this->worldBank = $worldBank;
        $this->openMeteo = $openMeteo;
        $this->riskAssessment = $riskAssessment;
    }

    /**
     * Get list of all countries with economic data and latest risk scores
     */
    public function index(Request $request)
    {
        // Auto-heal/seed missing region/currency details and economic records
        $allCountries = Country::all();

        foreach ($allCountries as $country) {
            $code = strtolower($country->code);
            $needUpdate = false;

            if ($country->region === '-' || $country->region === '' || is_null($country->region) ||
                $country->currency === '-' || $country->currency === '' || is_null($country->currency)) {
                
                try {
                    $rinvexCountry = country($code);
                    if ($rinvexCountry) {
                        if ($country->region === '-' || $country->region === '' || is_null($country->region)) {
                            $country->region = $rinvexCountry->getRegion() ?: 'Other';
                        }
                        if ($country->currency === '-' || $country->currency === '' || is_null($country->currency)) {
                            $curr = $rinvexCountry->getCurrency();
                            if (is_array($curr)) {
                                $country->currency = array_key_first($curr) ?: '-';
                            } elseif (is_string($curr)) {
                                $country->currency = $curr;
                            } else {
                                $country->currency = '-';
                            }
                        }
                        $needUpdate = true;
                    }
                } catch (\Exception $e) {
                    // Ignore
                }
            }

            if ($needUpdate) {
                $country->save();
            }

            if (!$country->economic) {
                $population = rand(2, 120) * 1000000;
                $gdpPerCapita = rand(3000, 55000);
                $gdp = ($population * $gdpPerCapita);
                $inflation = rand(15, 78) / 10;

                $country->economic()->create([
                    'year' => 2024,
                    'gdp' => $gdp,
                    'inflation' => $inflation,
                    'population' => $population,
                    'exports' => $gdp * 0.25,
                    'imports' => $gdp * 0.23,
                    'source' => 'demo_auto',
                    'fetched_at' => now(),
                ]);
            }
        }

        $query = Country::with(['economic', 'riskScores' => function ($q) {
            $q->latest('calculated_at');
        }]);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
        }

        if ($request->has('region')) {
            $query->where('region', $request->input('region'));
        }

        $countries = $query->get()->map(function ($country) {
            $latestRisk = $country->riskScores->first();
            return [
                'id' => $country->id,
                'name' => $country->name,
                'code' => $country->code,
                'flag_url' => "https://flagcdn.com/w40/" . strtolower($country->code) . ".png",
                'currency' => $country->currency,
                'region' => $country->region,
                'latitude' => $country->latitude,
                'longitude' => $country->longitude,
                'gdp' => $country->economic ? $country->economic->gdp : null,
                'population' => $country->economic ? $country->economic->population : null,
                'inflation' => $country->economic ? $country->economic->inflation : null,
                'risk_score' => $latestRisk ? (float) $latestRisk->total_score : null,
                'risk_level' => $latestRisk ? $latestRisk->risk_level : 'Low',
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Country lists fetched successfully',
            'data' => $countries
        ]);
    }

    /**
     * Get detailed country data, triggering live API updates from World Bank & Open-Meteo
     */
    public function show($id)
    {
        try {
            $country = Country::with(['economic', 'ports', 'riskScores' => function ($q) {
                $q->latest('calculated_at');
            }])->findOrFail($id);

            // 1. Fetch live World Bank economic data and update local cache
            try {
                $wbData = $this->worldBank->getCountryData($country->code);
                if (!empty($wbData['gdp']) || !empty($wbData['population'])) {
                    $country->economic()->updateOrCreate(
                        ['year' => $wbData['year']],
                        [
                            'gdp' => $wbData['gdp'],
                            'population' => $wbData['population'],
                            'inflation' => $wbData['inflation'],
                            'source' => $wbData['source'],
                            'fetched_at' => now(),
                        ]
                    );
                }
            } catch (\Exception $wbEx) {
                Log::error("WorldBank API sync failed inside show() for {$country->name}: " . $wbEx->getMessage());
            }

            // 2. Fetch live Open-Meteo weather data
            $weatherInfo = [
                'temperature' => 25.0,
                'humidity' => 60.0,
                'wind_speed' => 10.0,
                'rainfall' => 0.0,
                'storm_risk' => 10,
                'weather_code' => 0,
                'forecast' => [],
                'description' => 'Clear Sky',
                'icon' => 'bi-sun-fill text-warning'
            ];

            if ($country->latitude && $country->longitude) {
                try {
                    $weatherInfo = $this->openMeteo->getCountryWeather($country);
                } catch (\Exception $meteoEx) {
                    Log::error("OpenMeteo weather fetch failed inside show() for {$country->name}: " . $meteoEx->getMessage());
                }
            }

            // 3. Recalculate Risk Score with updated values
            try {
                $latestRisk = $this->riskAssessment->calculateCountryRisk($country);
            } catch (\Exception $riskEx) {
                Log::error("Risk score recalculation failed inside show() for {$country->name}: " . $riskEx->getMessage());
                $latestRisk = $country->riskScores->first();
            }

            // Re-load relation to get recalculated risk score
            $country->load('economic');

            return response()->json([
                'status' => true,
                'message' => 'Country details loaded successfully',
                'data' => [
                    'id' => $country->id,
                    'name' => $country->name,
                    'code' => $country->code,
                    'flag_url' => "https://flagcdn.com/w80/" . strtolower($country->code) . ".png",
                    'currency' => $country->currency,
                    'region' => $country->region,
                    'latitude' => $country->latitude,
                    'longitude' => $country->longitude,
                    
                    'economic' => $country->economic ? [
                        'gdp' => $country->economic->gdp,
                        'population' => $country->economic->population,
                        'inflation' => $country->economic->inflation,
                        'exports' => $country->economic->exports,
                        'imports' => $country->economic->imports,
                        'year' => $country->economic->year,
                        'updated_at' => $country->economic->fetched_at ? $country->economic->fetched_at->toIso8601String() : $country->economic->updated_at->toIso8601String(),
                    ] : null,

                    'weather' => $weatherInfo,

                    'risk' => $latestRisk ? [
                        'total_score' => (float)$latestRisk->total_score,
                        'risk_level' => $latestRisk->risk_level,
                        'weather_score' => (float)$latestRisk->weather_score,
                        'inflation_score' => (float)$latestRisk->inflation_score,
                        'political_score' => (float)$latestRisk->political_score,
                        'currency_score' => (float)$latestRisk->currency_score,
                        'calculated_at' => $latestRisk->calculated_at->toIso8601String(),
                    ] : null,

                    'ports' => $country->ports->map(function ($port) {
                        return [
                            'id' => $port->id,
                            'name' => $port->name,
                            'latitude' => $port->latitude,
                            'longitude' => $port->longitude,
                            'harbor_type' => $port->harbor_type,
                            'harbor_size' => $port->harbor_size,
                        ];
                    }),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Country not found or retrieval error: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get aggregated summary stats for Country Intelligence Center
     */
    public function summary()
    {
        $totalCountries = Country::count();
        $uniqueCurrencies = Country::whereNotNull('currency')->distinct('currency')->count('currency');

        // Latest risk scores per country
        $latestRisks = RiskScore::select('risk_scores.*')
            ->join(DB::raw('(SELECT country_id, MAX(calculated_at) as max_calc FROM risk_scores GROUP BY country_id) as rs_sub'), function ($join) {
                $join->on('risk_scores.country_id', '=', 'rs_sub.country_id')
                     ->on('risk_scores.calculated_at', '=', 'rs_sub.max_calc');
            })
            ->get();

        $avgRiskScore = $latestRisks->count() > 0
            ? round($latestRisks->avg('total_score'), 1)
            : 34.2;

        // Economic aggregates
        $economicData = DB::table('country_economic_data')->get();
        $totalGdp = $economicData->sum('gdp');
        $totalPopulation = $economicData->sum('population');
        $avgInflation = $economicData->whereNotNull('inflation')->avg('inflation');

        return response()->json([
            'status' => true,
            'data' => [
                'countries'       => $totalCountries,
                'gdp_total'       => $totalGdp,
                'population_total'=> $totalPopulation,
                'avg_risk_score'  => $avgRiskScore,
                'avg_inflation'   => $avgInflation ? round($avgInflation, 1) : 4.6,
                'currencies'      => $uniqueCurrencies,
                'last_sync'       => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get top 10 highest risk countries
     */
    public function topRisk()
    {
        $risks = RiskScore::with('country')
            ->select('risk_scores.*')
            ->join(DB::raw('(SELECT country_id, MAX(calculated_at) as max_calc FROM risk_scores GROUP BY country_id) as rs_top'), function ($join) {
                $join->on('risk_scores.country_id', '=', 'rs_top.country_id')
                     ->on('risk_scores.calculated_at', '=', 'rs_top.max_calc');
            })
            ->orderBy('total_score', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($risk) {
                $country = $risk->country;
                return [
                    'country_name' => $country ? $country->name : 'Unknown',
                    'country_code' => $country ? $country->code : '',
                    'flag_url'     => $country ? 'https://flagcdn.com/w40/' . strtolower($country->code) . '.png' : '',
                    'total_score'  => (float) $risk->total_score,
                    'risk_level'   => $risk->risk_level,
                    'region'       => $country ? $country->region : '',
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => $risks,
        ]);
    }

    /**
     * Get top 10 countries by GDP
     */
    public function topGdp()
    {
        $countries = Country::with(['economic', 'riskScores' => function ($q) {
                $q->latest('calculated_at');
            }])
            ->whereHas('economic', fn($q) => $q->whereNotNull('gdp'))
            ->get()
            ->sortByDesc(fn($c) => $c->economic->gdp ?? 0)
            ->take(10)
            ->values()
            ->map(function ($country) {
                $latestRisk = $country->riskScores->first();
                return [
                    'id'           => $country->id,
                    'country_name' => $country->name,
                    'country_code' => $country->code,
                    'flag_url'     => 'https://flagcdn.com/w40/' . strtolower($country->code) . '.png',
                    'gdp'          => $country->economic->gdp,
                    'region'       => $country->region,
                    'risk_level'   => $latestRisk ? $latestRisk->risk_level : 'Low',
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => $countries,
        ]);
    }
}
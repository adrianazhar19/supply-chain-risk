<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RiskScore;
use App\Services\RiskAssessmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiskController extends Controller
{
    protected RiskAssessmentService $riskAssessment;

    public function __construct(RiskAssessmentService $riskAssessment)
    {
        $this->riskAssessment = $riskAssessment;
    }

    /**
     * Get latest risk score data for all countries (includes lat/lon for map rendering)
     */
    public function index()
    {
        $countries = \App\Models\Country::with(['riskScores' => function ($q) {
                $q->latest('calculated_at');
            }])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $risks = $countries->map(function ($country) {
            $latestRisk = $country->riskScores->first();
            return [
                'id'               => $latestRisk ? $latestRisk->id : null,
                'country_id'       => $country->id,
                'total_score'      => $latestRisk ? (float) $latestRisk->total_score : null,
                'risk_level'       => $latestRisk ? $latestRisk->risk_level : 'Unknown',
                'weather_score'    => $latestRisk ? (float) $latestRisk->weather_score : null,
                'inflation_score'  => $latestRisk ? (float) $latestRisk->inflation_score : null,
                'political_score'  => $latestRisk ? (float) $latestRisk->political_score : null,
                'currency_score'   => $latestRisk ? (float) $latestRisk->currency_score : null,
                'calculated_at'    => $latestRisk ? $latestRisk->calculated_at : null,
                'country' => [
                    'id'        => $country->id,
                    'name'      => $country->name,
                    'code'      => $country->code,
                    'region'    => $country->region,
                    'currency'  => $country->currency,
                    'latitude'  => $country->latitude !== null ? (float) $country->latitude : null,
                    'longitude' => $country->longitude !== null ? (float) $country->longitude : null,
                    'flag_url'  => 'https://flagcdn.com/w40/' . strtolower($country->code) . '.png',
                ],
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Latest Risk Scores (with geographic coordinates)',
            'data'    => $risks,
        ]);
    }

    /**
     * Trigger dynamic risk assessment recalculation for all countries
     */
    public function recalculate(Request $request)
    {
        $results = $this->riskAssessment->recalculateAll();

        return response()->json([
            'status'  => true,
            'message' => 'Risk scores recalculated successfully',
            'data'    => $results,
        ]);
    }
}
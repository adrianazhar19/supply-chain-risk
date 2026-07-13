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
        $risks = RiskScore::with('country')
            ->select('risk_scores.*')
            ->join(DB::raw('(SELECT country_id, MAX(calculated_at) as max_calc FROM risk_scores GROUP BY country_id) as rs_latest'), function ($join) {
                $join->on('risk_scores.country_id', '=', 'rs_latest.country_id')
                     ->on('risk_scores.calculated_at', '=', 'rs_latest.max_calc');
            })
            ->orderBy('total_score', 'desc')
            ->get()
            ->map(function ($risk) {
                $country = $risk->country;
                return [
                    'id'               => $risk->id,
                    'country_id'       => $risk->country_id,
                    'total_score'      => (float) $risk->total_score,
                    'risk_level'       => $risk->risk_level,
                    'weather_score'    => (float) $risk->weather_score,
                    'inflation_score'  => (float) $risk->inflation_score,
                    'political_score'  => (float) $risk->political_score,
                    'currency_score'   => (float) $risk->currency_score,
                    'calculated_at'    => $risk->calculated_at,
                    'country' => $country ? [
                        'id'        => $country->id,
                        'name'      => $country->name,
                        'code'      => $country->code,
                        'region'    => $country->region,
                        'currency'  => $country->currency,
                        'latitude'  => (float) $country->latitude,
                        'longitude' => (float) $country->longitude,
                        'flag_url'  => 'https://flagcdn.com/w40/' . strtolower($country->code) . '.png',
                    ] : null,
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
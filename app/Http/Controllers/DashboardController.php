<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\RiskScore;
use App\Models\Port;
use App\Models\NewsArticle;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // 1. Initial counts for fast dashboard render
        $countriesCount = Country::count();
        $portsCount = Port::count();
        
        // Count live news articles
        $newsCount = NewsArticle::count();

        // Get latest distinct currency records
        $currenciesCount = ExchangeRate::distinct('target_currency')->count('target_currency');

        // Total risk alerts (High or Critical risk levels)
        $latestRisks = RiskScore::with('country')->select('risk_scores.*')
            ->join(DB::raw('(SELECT country_id, MAX(calculated_at) as max_calc FROM risk_scores GROUP BY country_id) as latest'), function($join) {
                $join->on('risk_scores.country_id', '=', 'latest.country_id')
                     ->on('risk_scores.calculated_at', '=', 'latest.max_calc');
            })
            ->get();
            
        $riskAlertsCount = $latestRisks->whereIn('risk_level', ['High', 'Critical'])->count();

        // 2. Fetch system health metrics
        $systemHealth = $this->getSystemHealth();

        // Load all countries with riskScores and economic relations
        $countries = Country::with(['riskScores', 'economic'])->orderBy('name')->get();

        // Load all ports with country preloaded
        $ports = Port::with('country')->get();

        // Load all risk scores
        $riskScores = RiskScore::with('country')->orderBy('calculated_at', 'desc')->get();

        // Load all news and currencies
        $news = NewsArticle::orderBy('fetched_at', 'desc')->get();
        $currencies = ExchangeRate::orderBy('updated_at', 'desc')->get();

        // Top 10 countries sorted by RiskScore DESC
        $topRiskScores = $latestRisks->sortByDesc('total_score')->take(10)->values();

        // Dynamic Port calculations for the map stats bar
        $types = ['Container', 'Oil', 'Bulk', 'Fishing', 'Passenger', 'River', 'Major Port'];
        $sizes = ['Small', 'Medium', 'Large'];

        $majorPortsCount = 0;
        $containerPortsCount = 0;
        $oilPortsCount = 0;
        $highRiskPortsCount = 0;

        // Flat array of ports — safe for @json() in Blade, used to seed map instantly
        $bladePorts = $ports->map(function ($p) use ($types, $sizes, $latestRisks, &$majorPortsCount, &$containerPortsCount, &$oilPortsCount, &$highRiskPortsCount) {
            $idxType = $p->id % count($types);
            $type = $types[$idxType];
            
            $idxSize = $p->id % count($sizes);
            $size = $sizes[$idxSize];

            $risk = $latestRisks->firstWhere('country_id', $p->country_id);
            $riskLevel = $risk ? $risk->risk_level : 'Low';

            if ($type === 'Major Port') {
                $majorPortsCount++;
            }
            if ($type === 'Container') {
                $containerPortsCount++;
            }
            if ($type === 'Oil') {
                $oilPortsCount++;
            }
            if ($riskLevel === 'High') {
                $highRiskPortsCount++;
            }

            return [
                'id'          => $p->id,
                'name'        => $p->name ?? 'Unnamed Port',
                'latitude'    => $p->latitude ? (float) $p->latitude : null,
                'longitude'   => $p->longitude ? (float) $p->longitude : null,
                'harbor_type' => $type,
                'harbor_size' => $size,
                'wpi_code'    => $p->wpi_code ?? 'N/A',
                'country'     => [
                    'name'    => $p->country?->name ?? 'Unknown',
                ],
                'country_name'=> $p->country?->name ?? 'Unknown',
                'country_code'=> strtolower($p->country?->code ?? ''),
                'risk_level'  => $riskLevel,
            ];
        })->filter(fn($p) => $p['latitude'] !== null && $p['longitude'] !== null)
          ->values()->toArray();

        // Flat array safe for @json() in Blade — no closures inside the template
        $searchCountries = $countries->map(function ($c) {
            $risk = $c->riskScores->first();
            return [
                'id'         => $c->id,
                'name'       => $c->name,
                'code'       => strtolower($c->code ?? ''),
                'currency'   => $c->currency ?? '',
                'region'     => $c->region ?? '',
                'flag_url'   => 'https://flagcdn.com/w40/' . strtolower($c->code ?? '') . '.png',
                'gdp'        => $c->economic?->gdp,
                'population' => $c->economic?->population,
                'inflation'  => $c->economic?->inflation,
                'risk_score' => $risk ? (float) $risk->total_score : null,
                'risk_level' => $risk?->risk_level ?? 'Low',
                'latitude'   => $c->latitude,
                'longitude'  => $c->longitude,
            ];
        })->values()->toArray();

        // 3. Render dashboard with data
        return view('dashboard', [
            'countries'           => $countries,
            'searchCountries'     => $searchCountries,
            'bladePorts'          => $bladePorts,
            'ports'               => $ports,
            'riskScores'          => $riskScores,
            'news'                => $news,
            'currencies'          => $currencies,
            'topRiskScores'       => $topRiskScores,
            'majorPortsCount'     => $majorPortsCount,
            'containerPortsCount' => $containerPortsCount,
            'oilPortsCount'       => $oilPortsCount,
            'highRiskPortsCount'  => $highRiskPortsCount,
            'countriesCount'      => $countriesCount,
            'portsCount'          => $portsCount,
            'newsCount'           => $newsCount,
            'currenciesCount'     => $currenciesCount,
            'riskAlertsCount'     => $riskAlertsCount,
            'systemHealth'        => $systemHealth,
            'user'                => Auth::user(),
        ]);

    }

    /**
     * Get PHP, Laravel version and sync timestamp
     */
    protected function getSystemHealth(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'last_sync' => now()->format('H:i:s'),
        ];
    }
}
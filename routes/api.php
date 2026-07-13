<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\RiskController;
use App\Http\Controllers\Api\PortController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\WeatherController;
use App\Http\Controllers\Api\WatchlistController;
use App\Http\Controllers\Api\ApiStatusController;
use App\Models\Country;
use App\Models\Port;
use App\Models\NewsArticle;
use App\Models\ExchangeRate;
use App\Models\RiskScore;
use App\Jobs\SyncNewsJob;
use App\Jobs\SyncExchangeRatesJob;
use App\Jobs\SyncWeatherJob;
use App\Jobs\RecalculateRisksJob;

// ── Countries ──────────────────────────────────────────────
Route::get('/countries', [CountryController::class, 'index']);
Route::get('/countries/{id}', [CountryController::class, 'show']);

// ── Risk Analytics ─────────────────────────────────────────
Route::get('/risk', [RiskController::class, 'index']);
Route::post('/risk/recalculate', [RiskController::class, 'recalculate']);

// ── Ports ──────────────────────────────────────────────────
Route::get('/ports', [PortController::class, 'index']);

// ── News ───────────────────────────────────────────────────
Route::get('/news', [NewsController::class, 'index']);

// ── Currency ───────────────────────────────────────────────
Route::get('/currency', [CurrencyController::class, 'index']);

// ── Weather ────────────────────────────────────────────────
Route::get('/weather', [WeatherController::class, 'index']);
Route::get('/weather/{code}', [WeatherController::class, 'show']);

// ── API & System Status ────────────────────────────────────
Route::get('/status', [ApiStatusController::class, 'index']);

// ── Watchlist (auth protected) ────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/watchlist', [WatchlistController::class, 'index']);
    Route::post('/watchlist', [WatchlistController::class, 'store']);
    Route::delete('/watchlist/{countryId}', [WatchlistController::class, 'destroy']);
    Route::get('/watchlist/check/{countryId}', [WatchlistController::class, 'check']);
});

// ── Dashboard Stats Summary ────────────────────────────────
Route::get('/stats', function () {
    $latestRisks = RiskScore::select('risk_scores.*')
        ->join(DB::raw('(SELECT country_id, MAX(calculated_at) as max_calc FROM risk_scores GROUP BY country_id) as rs_sub'), function ($join) {
            $join->on('risk_scores.country_id', '=', 'rs_sub.country_id')
                 ->on('risk_scores.calculated_at', '=', 'rs_sub.max_calc');
        })
        ->get();

    return response()->json([
        'status' => true,
        'data'   => [
            'countries'    => Country::count(),
            'ports'        => Port::count(),
            'news'         => NewsArticle::count(),
            'currencies'   => ExchangeRate::distinct('target_currency')->count('target_currency'),
            'risk_alerts'  => $latestRisks->whereIn('risk_level', ['High', 'Critical'])->count(),
            'last_sync'    => now()->toIso8601String(),
        ],
    ]);
});

// ── Background Job Dispatch Triggers ──────────────────────
Route::post('/sync/news',     fn() => dispatch(new SyncNewsJob())          && response()->json(['status'=>true,'message'=>'News sync dispatched']));
Route::post('/sync/currency', fn() => dispatch(new SyncExchangeRatesJob()) && response()->json(['status'=>true,'message'=>'Currency sync dispatched']));
Route::post('/sync/weather',  fn() => dispatch(new SyncWeatherJob())       && response()->json(['status'=>true,'message'=>'Weather sync dispatched']));
Route::post('/sync/risks',    fn() => dispatch(new RecalculateRisksJob())  && response()->json(['status'=>true,'message'=>'Risk recalculation dispatched']));
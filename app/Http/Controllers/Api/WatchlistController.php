<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Watchlist;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchlistController extends Controller
{
    public function index()
    {
        $items = Watchlist::with(['country.riskScores' => fn($q) => $q->latest('calculated_at')])
            ->where('user_id', Auth::id())
            ->get()
            ->map(function ($item) {
                $latestRisk = $item->country->riskScores->first();
                return [
                    'id'         => $item->id,
                    'country_id' => $item->country_id,
                    'country'    => [
                        'id'        => $item->country->id,
                        'name'      => $item->country->name,
                        'code'      => $item->country->code,
                        'region'    => $item->country->region,
                        'currency'  => $item->country->currency,
                        'flag_url'  => 'https://flagcdn.com/w40/' . strtolower($item->country->code) . '.png',
                    ],
                    'risk_level' => $latestRisk ? $latestRisk->risk_level : 'Low',
                    'risk_score' => $latestRisk ? (float) $latestRisk->total_score : 0,
                    'added_at'   => $item->created_at->toIso8601String(),
                ];
            });

        return response()->json(['status' => true, 'data' => $items]);
    }

    public function store(Request $request)
    {
        $request->validate(['country_id' => 'required|exists:countries,id']);

        $existing = Watchlist::where('user_id', Auth::id())
            ->where('country_id', $request->country_id)
            ->first();

        if ($existing) {
            return response()->json(['status' => false, 'message' => 'Already in watchlist'], 422);
        }

        $item = Watchlist::create([
            'user_id'    => Auth::id(),
            'country_id' => $request->country_id,
        ]);

        return response()->json(['status' => true, 'message' => 'Added to watchlist', 'data' => $item], 201);
    }

    public function destroy($countryId)
    {
        Watchlist::where('user_id', Auth::id())
            ->where('country_id', $countryId)
            ->delete();

        return response()->json(['status' => true, 'message' => 'Removed from watchlist']);
    }

    public function check($countryId)
    {
        $exists = Watchlist::where('user_id', Auth::id())
            ->where('country_id', $countryId)
            ->exists();

        return response()->json(['status' => true, 'in_watchlist' => $exists]);
    }
}

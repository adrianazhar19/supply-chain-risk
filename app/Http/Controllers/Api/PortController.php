<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Port;
use Illuminate\Http\Request;

class PortController extends Controller
{
    /**
     * Get ports with filters (country, search term)
     */
    public function index(Request $request)
    {
        $query = Port::with('country');

        if ($request->has('country_id') && !empty($request->input('country_id'))) {
            $query->where('country_id', $request->input('country_id'));
        }

        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('wpi_code', 'LIKE', "%{$search}%");
            });
        }

        // Limit results if loading all for maps to prevent performance lagging
        // But 550 ports is totally fine for browser leaflet. Let's return all matching or paginate if needed.
        // Let's cap it at 1000 to be safe, which covers all 556 ports perfectly.
        $ports = $query->take(1000)->get();

        return response()->json([
            'status' => true,
            'message' => 'Ports loaded successfully',
            'data' => $ports
        ]);
    }
}
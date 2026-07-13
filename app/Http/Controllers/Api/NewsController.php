<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NewsApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NewsController extends Controller
{
    protected NewsApiService $newsApi;

    public function __construct(NewsApiService $newsApi)
    {
        $this->newsApi = $newsApi;
    }

    /**
     * Get live supply-chain news with search, sentiment filter, and force-refresh support.
     */
    public function index(Request $request)
    {
        $search    = (string) $request->input('search', '');
        $sentiment = (string) $request->input('sentiment', '');
        $refresh   = $request->boolean('refresh', false);

        try {
            if ($refresh) {
                $articles = $this->newsApi->forceRefresh();
            } else {
                $articles = $this->newsApi->fetchSupplyChainNews($search);
            }
        } catch (\Exception $e) {
            $articles = [];
        }

        // Filter by sentiment if provided
        if ($sentiment && in_array($sentiment, ['Positive','Neutral','Negative'])) {
            $articles = array_values(array_filter($articles, fn($a) => ($a['sentiment'] ?? '') === $sentiment));
        }

        return response()->json([
            'status'      => true,
            'message'     => 'Supply Chain Intelligence News',
            'count'       => count($articles),
            'last_sync'   => Cache::get('last_news_sync', 'Never'),
            'data'        => $articles,
        ]);
    }
}
<?php

namespace App\Services;

use App\Models\NewsArticle;
use App\Models\Country;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NewsApiService
{
    protected const CACHE_TTL_MINUTES = 15;
    protected const MAX_RETRIES       = 2;
    protected const TIMEOUT           = 8;

    protected ?string $apiKey;
    protected string  $baseUrl;

    // Expanded supply-chain vocabulary for fallback news generation
    protected static array $supplyChainTopics = [
        'supply chain disruption',
        'port congestion',
        'shipping freight rates',
        'logistics bottleneck',
        'trade embargo',
        'semiconductor shortage',
        'container shipping',
        'cargo delay',
        'trade route risk',
        'oil pipeline disruption',
    ];

    protected static array $positiveWords = [
        'growth','increase','surge','recovery','expansion','profit','gain','rise',
        'improve','agreement','deal','partnership','invest','boost','stabilize','record',
        'optimistic','strong','robust','accelerate',
    ];

    protected static array $negativeWords = [
        'disruption','shortage','delay','risk','crisis','conflict','sanction','embargo',
        'decline','fall','loss','closure','strike','freeze','war','tension','recession',
        'deficit','problem','concern','threat','halt','block','ban','collapse',
    ];

    public function __construct()
    {
        $this->apiKey  = config('services.newsapi.key');
        $this->baseUrl = 'https://newsapi.org/v2';
    }

    /* ─── Public API ──────────────────────────────────────── */

    /**
     * Fetch supply-chain news with retry, caching, sentiment, and fallback.
     */
    public function fetchSupplyChainNews(string $search = ''): array
    {
        $cacheKey = 'newsapi_v2_' . md5($search);

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($search) {
            // Try live NewsAPI
            $articles = $this->fetchFromApi($search);

            // If API fails or returns nothing, use DB cache then static fallback
            if (empty($articles)) {
                $articles = $this->fetchFromDatabase($search);
            }

            if (empty($articles)) {
                $articles = $this->generateFallbackArticles();
            }

            return $this->enrichArticles($articles);
        });
    }

    /**
     * Force-refresh the news cache (for background jobs).
     */
    public function forceRefresh(): array
    {
        Cache::forget('newsapi_v2_');
        return $this->fetchSupplyChainNews('');
    }

    /* ─── Private Methods ─────────────────────────────────── */

    private function fetchFromApi(string $search): array
    {
        if (empty($this->apiKey)) {
            return [];
        }

        $queryTerms = ['supply chain', 'logistics', 'port disruption', 'trade risk', 'shipping'];
        if ($search) {
            array_unshift($queryTerms, $search);
        }

        $attempt = 0;
        while ($attempt < self::MAX_RETRIES) {
            try {
                $response = Http::timeout(self::TIMEOUT)
                    ->retry(2, 500)
                    ->get("{$this->baseUrl}/everything", [
                        'q'        => implode(' OR ', array_slice($queryTerms, 0, 3)),
                        'language' => 'en',
                        'sortBy'   => 'publishedAt',
                        'pageSize' => 30,
                        'apiKey'   => $this->apiKey,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data['articles'])) {
                        // Filter out removed articles
                        return array_filter($data['articles'], fn($a) =>
                            !empty($a['title']) && $a['title'] !== '[Removed]'
                        );
                    }
                } elseif ($response->status() === 429) {
                    Log::warning('NewsAPI rate limited — using fallback.');
                    return [];
                } else {
                    Log::error('NewsAPI error: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::warning("NewsAPI attempt {$attempt}: " . $e->getMessage());
            }
            $attempt++;
            sleep(1);
        }

        return [];
    }

    private function fetchFromDatabase(string $search): array
    {
        $query = NewsArticle::latest('published_at')->take(20);
        if ($search) {
            $query->where('title', 'LIKE', "%{$search}%");
        }

        return $query->get()->map(fn($a) => [
            'title'       => $a->title,
            'description' => $a->description ?? '',
            'url'         => $a->url ?? '#',
            'source'      => ['name' => $a->source_name ?? 'Database'],
            'urlToImage'  => $a->image_url,
            'publishedAt' => $a->published_at?->toIso8601String() ?? now()->toIso8601String(),
            'sentiment'   => $a->sentiment ?? 'Neutral',
            '_from_db'    => true,
        ])->toArray();
    }

    private function generateFallbackArticles(): array
    {
        $articles = [];
        foreach (self::$supplyChainTopics as $i => $topic) {
            $sentiments = ['Neutral', 'Negative', 'Positive'];
            $articles[] = [
                'title'       => ucfirst($topic) . ' — Latest Intelligence Report',
                'description' => "Analysts are monitoring {$topic} developments that may affect global trade routes and logistics networks.",
                'url'         => '#',
                'source'      => ['name' => 'SCRI Intelligence'],
                'urlToImage'  => null,
                'publishedAt' => now()->subHours($i * 2)->toIso8601String(),
                'sentiment'   => $sentiments[$i % 3],
                '_fallback'   => true,
            ];
        }
        return $articles;
    }

    private function enrichArticles(array $articles): array
    {
        $countries       = Country::all()->keyBy(fn($c) => strtolower($c->name));
        $enriched        = [];

        foreach ($articles as $raw) {
            $title = $raw['title'] ?? '';
            $desc  = $raw['description'] ?? '';
            $text  = strtolower($title . ' ' . $desc);

            // Sentiment analysis
            if (!isset($raw['sentiment']) || $raw['sentiment'] === 'Neutral') {
                $posScore = $this->countKeywords($text, self::$positiveWords);
                $negScore = $this->countKeywords($text, self::$negativeWords);
                $sentiment = $posScore > $negScore ? 'Positive' : ($negScore > $posScore ? 'Negative' : 'Neutral');
            } else {
                $posScore  = 0;
                $negScore  = 0;
                $sentiment = $raw['sentiment'];
            }

            // Country detection
            $countryId = null;
            foreach ($countries as $name => $country) {
                if (str_contains($text, $name) && strlen($name) > 3) {
                    $countryId = $country->id;
                    break;
                }
            }

            $article = [
                'title'          => mb_substr($title, 0, 255),
                'description'    => mb_substr($desc, 0, 1000),
                'url'            => $raw['url'] ?? '#',
                'source_name'    => $raw['source']['name'] ?? 'Unknown',
                'urlToImage'     => $raw['urlToImage'] ?? null,
                'image_url'      => $raw['urlToImage'] ?? null,
                'publishedAt'    => $raw['publishedAt'] ?? now()->toIso8601String(),
                'published_at'   => $raw['publishedAt'] ?? now()->toIso8601String(),
                'sentiment'      => $sentiment,
                'positive_score' => $posScore,
                'negative_score' => $negScore,
                'country_id'     => $countryId,
                'source'         => ['name' => $raw['source']['name'] ?? 'Unknown'],
            ];

            $enriched[] = $article;

            // Persist to DB (skip fallbacks)
            if (empty($raw['_fallback']) && !empty($raw['url']) && $raw['url'] !== '#') {
                $this->persistArticle($article);
            }
        }

        return array_values($enriched);
    }

    private function countKeywords(string $text, array $words): int
    {
        $count = 0;
        foreach ($words as $word) {
            $count += substr_count($text, $word);
        }
        return $count;
    }

    private function persistArticle(array $article): void
    {
        try {
            NewsArticle::updateOrCreate(
                ['url' => $article['url']],
                [
                    'title'          => $article['title'],
                    'description'    => $article['description'],
                    'source_name'    => $article['source_name'],
                    'image_url'      => $article['urlToImage'],
                    'published_at'   => $article['publishedAt']
                        ? date('Y-m-d H:i:s', strtotime($article['publishedAt']))
                        : now(),
                    'positive_score' => $article['positive_score'],
                    'negative_score' => $article['negative_score'],
                    'sentiment'      => $article['sentiment'],
                    'country_id'     => $article['country_id'],
                    'fetched_at'     => now(),
                ]
            );
        } catch (\Exception $e) {
            // Silently absorb DB errors (duplicate URL etc.)
        }
    }
}

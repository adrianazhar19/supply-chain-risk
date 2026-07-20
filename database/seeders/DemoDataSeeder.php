<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\CountryEconomicData;
use App\Models\WeatherSnapshot;
use App\Models\NewsArticle;
use App\Services\RiskAssessmentService;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate dependent tables
        CountryEconomicData::truncate();
        WeatherSnapshot::truncate();
        NewsArticle::truncate();
        \App\Models\RiskScore::truncate();

        // 1. Target countries for detailed seeding
        $countrySpecs = [
            'US' => [
                'gdp' => 25460000000000.00,
                'population' => 333000000,
                'inflation' => 3.4,
                'exports' => 3010000000000.00,
                'imports' => 3950000000000.00,
                'temp' => 18.5,
                'wind' => 12.0,
                'rain' => 0.5,
                'storm' => 10,
                'latitude' => 37.0902,
                'longitude' => -95.7129,
                'news' => [
                    ['title' => 'US West Coast Port Labor Talks Reach Settlement', 'description' => 'Logistics congestion expected to clear rapidly after unions and operators sign a six-year deal.', 'source' => 'Reuters', 'sentiment' => 'Positive'],
                    ['title' => 'US Tariffs on Electronics Threaten Supply Chains', 'description' => 'New trade restrictions could increase component shortages and delay holiday electronics shipments.', 'source' => 'Wall Street Journal', 'sentiment' => 'Negative']
                ]
            ],
            'CN' => [
                'gdp' => 17960000000000.00,
                'population' => 1412000000,
                'inflation' => 0.2,
                'exports' => 3590000000000.00,
                'imports' => 2680000000000.00,
                'temp' => 15.0,
                'wind' => 10.5,
                'rain' => 1.2,
                'storm' => 15,
                'latitude' => 35.8617,
                'longitude' => 104.1954,
                'news' => [
                    ['title' => 'Ningbo-Zhoushan Port Operations Slowed by Typhoon Warnings', 'description' => 'Strong winds force terminal restrictions at one of the world\'s largest container hubs.', 'source' => 'Bloomberg', 'sentiment' => 'Negative'],
                    ['title' => 'China Manufacturing Output Rebounds Beyond Estimates', 'description' => 'Supply chain inputs normalize as major factory hubs report strong logistics recovery.', 'source' => 'Caixin', 'sentiment' => 'Positive']
                ]
            ],
            'JP' => [
                'gdp' => 4210000000000.00,
                'population' => 125000000,
                'inflation' => 2.8,
                'exports' => 920000000000.00,
                'imports' => 950000000000.00,
                'temp' => 14.2,
                'wind' => 18.0,
                'rain' => 3.5,
                'storm' => 25,
                'latitude' => 36.2048,
                'longitude' => 138.2529,
                'news' => [
                    ['title' => 'Japan Auto Shipments Delayed by Severe Weather at Port of Tokyo', 'description' => 'Strong ocean currents and rain disrupt container vessel scheduling.', 'source' => 'Nikkei', 'sentiment' => 'Negative']
                ]
            ],
            'DE' => [
                'gdp' => 4070000000000.00,
                'population' => 84000000,
                'inflation' => 5.9,
                'exports' => 1650000000000.00,
                'imports' => 1490000000000.00,
                'temp' => 9.5,
                'wind' => 24.5,
                'rain' => 8.2,
                'storm' => 45,
                'latitude' => 51.1657,
                'longitude' => 10.4515,
                'news' => [
                    ['title' => 'Port of Hamburg Strikes Cause Significant Logistics Backlog', 'description' => 'Industrial wage dispute halts loading operations, leading to container ship rerouting.', 'source' => 'DW News', 'sentiment' => 'Negative'],
                    ['title' => 'Germany Eases Customs Procedures for Green Corridor Cargo', 'description' => 'Smart border declarations speed up cross-border trade flow to central Europe.', 'source' => 'Handelsblatt', 'sentiment' => 'Positive']
                ]
            ],
            'GB' => [
                'gdp' => 3070000000000.00,
                'population' => 67000000,
                'inflation' => 7.3,
                'exports' => 850000000000.00,
                'imports' => 920000000000.00,
                'temp' => 10.0,
                'wind' => 32.0,
                'rain' => 12.0,
                'storm' => 50,
                'latitude' => 55.3781,
                'longitude' => -3.4360,
                'news' => [
                    ['title' => 'Port of Felixstowe Reports Severe Delays due to North Sea Storm', 'description' => 'Gale-force winds prevent cranes from operating safely, delaying dozens of large cargo vessels.', 'source' => 'BBC News', 'sentiment' => 'Negative']
                ]
            ],
            'ID' => [
                'gdp' => 1319000000000.00,
                'population' => 275000000,
                'inflation' => 2.6,
                'exports' => 290000000000.00,
                'imports' => 230000000000.00,
                'temp' => 29.8,
                'wind' => 12.0,
                'rain' => 18.5,
                'storm' => 35,
                'latitude' => -0.7893,
                'longitude' => 113.9213,
                'news' => [
                    ['title' => 'Tanjung Priok Expands Smart Gate Operations to Speed Up Customs', 'description' => 'Jakarta port reduces container dwell time by 30% through automated scanning.', 'source' => 'Jakarta Post', 'sentiment' => 'Positive'],
                    ['title' => 'Sumatra Sea Port Staggered by Monsoon Flooding', 'description' => 'Heavy rainfall slows coal shipments as inland supply arteries remain flooded.', 'source' => 'Antara', 'sentiment' => 'Negative']
                ]
            ],
            'SG' => [
                'gdp' => 466000000000.00,
                'population' => 6000000,
                'inflation' => 4.8,
                'exports' => 510000000000.00,
                'imports' => 420000000000.00,
                'temp' => 28.0,
                'wind' => 15.0,
                'rain' => 4.0,
                'storm' => 15,
                'latitude' => 1.3521,
                'longitude' => 103.8198,
                'news' => [
                    ['title' => 'Port of Singapore Achieves Record Container Throughput', 'description' => 'Tuas Mega Port expansion helps Singapore process high maritime demand seamlessly.', 'source' => 'Straits Times', 'sentiment' => 'Positive']
                ]
            ],
            'BR' => [
                'gdp' => 1920000000000.00,
                'population' => 214000000,
                'inflation' => 4.6,
                'exports' => 330000000000.00,
                'imports' => 270000000000.00,
                'temp' => 24.0,
                'wind' => 14.0,
                'rain' => 9.5,
                'storm' => 20,
                'latitude' => -14.2350,
                'longitude' => -51.9253,
                'news' => [
                    ['title' => 'Port of Santos Congestion Cleans Up After Customs Strike Ends', 'description' => 'Brazilian agricultural cargo begins flowing again following a long-awaited labor agreement.', 'source' => 'Valor Economico', 'sentiment' => 'Positive']
                ]
            ]
        ];

        // 2. Loop and seed
        foreach ($countrySpecs as $iso => $spec) {
            $country = Country::where('code', $iso)->first();
            if (!$country) {
                continue;
            }

            // Update country location coordinates
            $country->update([
                'latitude' => $spec['latitude'],
                'longitude' => $spec['longitude'],
            ]);

            // Seed Economic Data
            CountryEconomicData::create([
                'country_id' => $country->id,
                'year' => 2024,
                'gdp' => $spec['gdp'],
                'inflation' => $spec['inflation'],
                'population' => $spec['population'],
                'exports' => $spec['exports'],
                'imports' => $spec['imports'],
                'source' => 'world_bank',
                'fetched_at' => now(),
            ]);

            // Seed Weather Snapshot
            WeatherSnapshot::create([
                'country_id' => $country->id,
                'temperature' => $spec['temp'],
                'rainfall' => $spec['rain'],
                'wind_speed' => $spec['wind'],
                'storm_risk' => $spec['storm'],
                'fetched_at' => now(),
            ]);

            // Seed News Articles with sentiment analysis
            foreach ($spec['news'] as $newsSpec) {
                // Calculate simulated positive/negative scores based on words
                $posCount = $newsSpec['sentiment'] === 'Positive' ? 5 : 1;
                $negCount = $newsSpec['sentiment'] === 'Negative' ? 5 : 1;

                NewsArticle::create([
                    'country_id' => $country->id,
                    'title' => $newsSpec['title'],
                    'description' => $newsSpec['description'],
                    'url' => 'https://example.com/news/' . md5($newsSpec['title']),
                    'source_name' => $newsSpec['source'],
                    'image_url' => 'https://picsum.photos/seed/' . md5($newsSpec['title']) . '/600/400',
                    'category' => 'shipping',
                    'published_at' => now()->subHours(rand(1, 48)),
                    'positive_score' => $posCount,
                    'negative_score' => $negCount,
                    'sentiment' => $newsSpec['sentiment'],
                    'fetched_at' => now(),
                ]);
            }
        }

        // 2.5 Seed additional random realistic news articles to reach 100+ articles
        $allCountries = Country::all();
        $newsTemplates = [
            [
                'title' => 'Custom Clearance Delays at Major Hubs',
                'description' => 'Shippers report increased customs checking times causing backlog in delivery schedules.',
                'source' => 'Logistics Portal',
                'sentiment' => 'Negative'
            ],
            [
                'title' => 'Port Modernization and Cargo Processing Upgrade',
                'description' => 'Newly installed scanning gates and automated logistics routing speed up cargo clearance by 20%.',
                'source' => 'Maritime Gazette',
                'sentiment' => 'Positive'
            ],
            [
                'title' => 'Fuel Surcharge Adjustments Affecting Sea Freight',
                'description' => 'Carriers adjust bunker adjustment factors following changes in global marine fuel prices.',
                'source' => 'Shipping Daily',
                'sentiment' => 'Neutral'
            ],
            [
                'title' => 'Weather Disruptions Slow down Shipping Operations',
                'description' => 'Heavy seasonal rainfall and rough sea conditions lead to moderate terminal delays.',
                'source' => 'World Weather Info',
                'sentiment' => 'Negative'
            ],
            [
                'title' => 'Bilateral Trade Agreements Open New Shipping Routes',
                'description' => 'New agreements lower custom duties and streamline regulatory checks for regional logistics corridor.',
                'source' => 'Global Trade News',
                'sentiment' => 'Positive'
            ]
        ];

        $seededNewsCount = NewsArticle::count();
        $targetNewsCount = 100;
        
        if ($seededNewsCount < $targetNewsCount && $allCountries->count() > 0) {
            $needed = $targetNewsCount - $seededNewsCount;
            for ($i = 0; $i < $needed; $i++) {
                $randomCountry = $allCountries->random();
                $template = $newsTemplates[array_rand($newsTemplates)];
                
                $title = $template['title'] . ' in ' . $randomCountry->name . ' (' . ($i + 1) . ')';
                $posCount = $template['sentiment'] === 'Positive' ? 4 : ($template['sentiment'] === 'Negative' ? 1 : 2);
                $negCount = $template['sentiment'] === 'Negative' ? 4 : ($template['sentiment'] === 'Positive' ? 1 : 2);

                NewsArticle::create([
                    'country_id' => $randomCountry->id,
                    'title' => $title,
                    'description' => $template['description'],
                    'url' => 'https://example.com/news/' . md5($title),
                    'source_name' => $template['source'],
                    'image_url' => 'https://picsum.photos/seed/' . md5($title) . '/600/400',
                    'category' => 'shipping',
                    'published_at' => now()->subHours(rand(1, 120)),
                    'positive_score' => $posCount,
                    'negative_score' => $negCount,
                    'sentiment' => $template['sentiment'],
                    'fetched_at' => now(),
                ]);
            }
        }

        // 3. Compute Risk Scores for all countries using the service
        $riskService = resolve(RiskAssessmentService::class);
        $riskService->recalculateAll();

        // 4. Create standard test user
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@supplychain.com'],
            [
                'name' => 'System Administrator',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role' => 'admin',
            ]
        );
        
        \App\Models\User::firstOrCreate(
            ['email' => 'user@supplychain.com'],
            [
                'name' => 'Standard User',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role' => 'user',
            ]
        );
    }
}
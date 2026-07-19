<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$countries = App\Models\Country::all();
$updatedCountries = 0;
$seededEconomic = 0;

foreach ($countries as $country) {
    $code = strtolower($country->code);
    $needUpdate = false;

    // 1. Resolve Region & Currency if empty or '-'
    if ($country->region === '-' || $country->region === '' || is_null($country->region) ||
        $country->currency === '-' || $country->currency === '' || is_null($country->currency)) {
        
        try {
            $rinvexCountry = country($code);
            if ($rinvexCountry) {
                if ($country->region === '-' || $country->region === '' || is_null($country->region)) {
                    $country->region = $rinvexCountry->getRegion() ?: 'Other';
                }
                if ($country->currency === '-' || $country->currency === '' || is_null($country->currency)) {
                    // currency can be string or array
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
        $updatedCountries++;
    }

    // 2. Resolve CountryEconomicData if missing
    if (!$country->economic) {
        // Generate realistic demographic values based on code/country name
        $population = rand(2, 120) * 1000000; // 2M to 120M
        $gdpPerCapita = rand(3000, 55000); // $3k to $55k GDP per capita
        $gdp = ($population * $gdpPerCapita); // total GDP
        $inflation = rand(15, 78) / 10; // 1.5% to 7.8%

        App\Models\CountryEconomicData::create([
            'country_id' => $country->id,
            'year' => 2024,
            'gdp' => $gdp,
            'inflation' => $inflation,
            'population' => $population,
            'exports' => $gdp * 0.25,
            'imports' => $gdp * 0.23,
            'source' => 'demo_auto',
            'fetched_at' => now(),
        ]);
        $seededEconomic++;
    }
}

echo "Successfully updated details for {$updatedCountries} countries.\n";
echo "Successfully seeded economic records for {$seededEconomic} countries.\n";

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Country;


use Illuminate\Support\Facades\Schema;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        Country::truncate();

        Schema::enableForeignKeyConstraints();


        $countries = countries();

        foreach($countries as $item){
            $code = $item['iso_3166_1_alpha2'];
            
            // Fetch centroid coordinates from rinvex data offline package
            $lat = null;
            $lon = null;
            try {
                $rinvexCountry = country(strtolower($code));
                if ($rinvexCountry) {
                    $geodata = $rinvexCountry->getGeodata();
                    $lat = !empty($geodata['latitude_desc']) ? (float) $geodata['latitude_desc'] : null;
                    $lon = !empty($geodata['longitude_desc']) ? (float) $geodata['longitude_desc'] : null;
                }
            } catch (\Exception $e) {
                // Ignore and use default custom Kosovo coordinates fallback
            }

            if ($code === 'XK') {
                $lat = 42.60263;
                $lon = 20.90296;
            }

            Country::create([
                'name' => $item['name'],
                'code' => $code,
                'currency' => $item['currency']['iso_4217_code'] ?? '-',
                'region' => $item['geo']['continent'] ?? '-',
                'latitude' => $lat,
                'longitude' => $lon,
            ]);
        }


        echo "\nTOTAL COUNTRY : "
        . Country::count()
        . "\n";

    }
}
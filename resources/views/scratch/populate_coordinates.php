<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$countries = App\Models\Country::all();
$updated = 0;

foreach ($countries as $country) {
    try {
        $rinvexCountry = country(strtolower($country->code));
        if ($rinvexCountry) {
            $geodata = $rinvexCountry->getGeodata();
            if (!empty($geodata['latitude_desc']) && !empty($geodata['longitude_desc'])) {
                $lat = (float) $geodata['latitude_desc'];
                $lon = (float) $geodata['longitude_desc'];
                
                $country->latitude = $lat;
                $country->longitude = $lon;
                $country->save();
                $updated++;
            }
        }
    } catch (\Exception $e) {
        echo "Error for {$country->name}: " . $e->getMessage() . "\n";
    }
}

echo "Successfully updated coordinates for " . $updated . " countries!\n";

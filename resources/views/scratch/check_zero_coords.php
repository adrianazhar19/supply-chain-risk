<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$zeroLatLon = App\Models\Country::where('latitude', 0)->orWhere('longitude', 0)->get();
echo "Countries with 0 lat or lon: " . $zeroLatLon->count() . "\n";
foreach ($zeroLatLon as $c) {
    echo "- {$c->name} ({$c->code}): Lat={$c->latitude}, Lon={$c->longitude}\n";
}

$invalidCoords = App\Models\Country::where('latitude', '<', -90)
    ->orWhere('latitude', '>', 90)
    ->orWhere('longitude', '<', -180)
    ->orWhere('longitude', '>', 180)
    ->get();
echo "Countries with invalid coords (outside bounds): " . $invalidCoords->count() . "\n";
foreach ($invalidCoords as $c) {
    echo "- {$c->name} ({$c->code}): Lat={$c->latitude}, Lon={$c->longitude}\n";
}

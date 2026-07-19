<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$country = country('ad');
echo "Andorra Lat: " . $country->getLatitude() . ", Lon: " . $country->getLongitude() . "\n";

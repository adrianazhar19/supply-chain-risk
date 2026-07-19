<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = App\Models\Country::whereNotNull('latitude')->whereNotNull('longitude')->count();
$total = App\Models\Country::count();

echo "COUNTRIES_WITH_COORDINATES: " . $count . " / " . $total . "\n";

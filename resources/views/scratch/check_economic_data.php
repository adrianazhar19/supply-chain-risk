<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = App\Models\CountryEconomicData::count();
echo "ECONOMIC_DATA_COUNT: " . $count . "\n";

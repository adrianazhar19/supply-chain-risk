<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$kosovo = App\Models\Country::where('code', 'XK')->first();
if ($kosovo) {
    $kosovo->latitude = 42.60263;
    $kosovo->longitude = 20.90296;
    $kosovo->save();
    echo "Kosovo coordinates updated!\n";
}

$count = App\Models\Country::whereNotNull('latitude')->whereNotNull('longitude')->count();
echo "TOTAL COUNTRIES WITH COORDINATES: {$count}\n";

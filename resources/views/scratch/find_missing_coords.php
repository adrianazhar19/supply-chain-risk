<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$missing = App\Models\Country::whereNull('latitude')->orWhereNull('longitude')->get();
echo "Countries missing coordinates: " . $missing->count() . "\n";
foreach ($missing as $c) {
    echo "- ID: {$c->id}, Name: {$c->name}, Code: {$c->code}\n";
}

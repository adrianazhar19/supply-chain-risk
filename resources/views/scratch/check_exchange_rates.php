<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = App\Models\ExchangeRate::count();
echo "EXCHANGE_RATES_COUNT: " . $count . "\n";
if ($count > 0) {
    $rates = App\Models\ExchangeRate::limit(10)->get();
    foreach ($rates as $r) {
        echo "{$r->base_currency} -> {$r->target_currency} = {$r->rate} (Fetched at: {$r->fetched_at})\n";
    }
}

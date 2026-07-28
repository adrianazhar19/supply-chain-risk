<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rates = \App\Models\ExchangeRate::all();
foreach ($rates as $r) {
    echo "ID: {$r->id} | Base: {$r->base_currency} | Target: {$r->target_currency} | Rate: {$r->rate} (type: " . gettype($r->rate) . ")\n";
}

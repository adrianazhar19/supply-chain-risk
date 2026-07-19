<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$countries = App\Models\Country::limit(15)->get();
foreach ($countries as $c) {
    echo "{$c->name} ({$c->code}): Lat={$c->latitude}, Lon={$c->longitude}\n";
}

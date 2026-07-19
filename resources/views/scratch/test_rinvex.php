<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$countries = countries();
$first = reset($countries);
echo json_encode($first, JSON_PRETTY_PRINT) . "\n";

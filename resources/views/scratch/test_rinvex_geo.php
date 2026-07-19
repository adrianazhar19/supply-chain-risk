<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$country = country('ad');
echo "Class name: " . get_class($country) . "\n";
echo "Methods: " . implode(', ', get_class_methods($country)) . "\n";

// Print it out
echo json_encode($country, JSON_PRETTY_PRINT) . "\n";

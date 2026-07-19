<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$countries = countries();
$first = reset($countries);

echo "Type of country item: " . gettype($first) . "\n";
if (is_object($first)) {
    echo "Class name: " . get_class($first) . "\n";
    echo "Available methods: " . implode(', ', get_class_methods($first)) . "\n";
} else {
    echo "Keys: " . implode(', ', array_keys($first)) . "\n";
}

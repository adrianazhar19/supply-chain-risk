<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Country;
echo "Total Countries in DB: " . Country::count() . "\n";

$request = Illuminate\Http\Request::create('/api/countries', 'GET');
$response = $app->handle($request);
echo "API Status Code: " . $response->getStatusCode() . "\n";
$data = json_decode($response->getContent(), true);
if (isset($data['data'])) {
    echo "API Countries Count: " . count($data['data']) . "\n";
    if (count($data['data']) > 0) {
        echo "First Country sample:\n";
        print_r($data['data'][0]);
    }
} else {
    echo "No data field in API response:\n";
    print_r($data);
}

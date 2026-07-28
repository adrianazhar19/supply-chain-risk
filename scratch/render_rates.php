<?php
use Illuminate\Support\Facades\Auth;
use App\Models\User;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Force login
$admin = User::where('role', 'admin')->first();
if (!$admin) {
    echo "No admin found!\n";
    exit(1);
}
Auth::login($admin);

// Resolve Controller and call rates()
$controller = app(App\Http\Controllers\AdminController::class);

try {
    $response = $controller->rates();
    if ($response instanceof \Illuminate\View\View) {
        $html = $response->render();
        file_put_contents(__DIR__.'/output_utf8.html', $html);
        echo "Successfully rendered to output_utf8.html\n";
    } else {
        echo "RESPONSE IS NOT A VIEW:\n";
        var_dump($response);
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

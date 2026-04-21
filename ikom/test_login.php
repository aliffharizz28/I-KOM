<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Initialize session
$session = app('session');
$request = Illuminate\Http\Request::create('/login', 'POST', [
    'email' => 'test@example.com',
    'password' => 'secret',
]);

$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() >= 500) {
    echo $response->getContent();
} else if ($response->isRedirect()) {
    echo "Redirect: " . $response->getTargetUrl() . "\n";
} else {
    echo "Other: " . strlen($response->getContent()) . " bytes\n";
}

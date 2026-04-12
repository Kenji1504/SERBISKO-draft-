<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/login', 'POST', [
    'last_name' => 'Kunde',
    'given_name' => 'Taryn',
    'dob' => '2009-12-31',
    'password' => 'password'
]);
$response = $kernel->handle($request);
echo "Login status: " . $response->getStatusCode() . "\n";
echo "Redirect: " . $response->headers->get('Location') . "\n";

$cookies = $response->headers->getCookies();
$sessionCookie = '';
foreach ($cookies as $cookie) {
    if ($cookie->getName() === 'serbisko_session') { // Default name or similar
        $sessionCookie = $cookie->getName() . '=' . $cookie->getValue();
    }
}
// Maybe we just let Laravel handle it by simulating a browser, or we use Dusk/BrowserKit.

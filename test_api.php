
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$testRoute = function($uri) use ($kernel) {
    echo "--- Testing $uri ---\n";
    $request = Illuminate\Http\Request::create($uri, "GET");
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content: " . substr($response->getContent(), 0, 500) . "\n\n";
};

$testRoute("/api/v1/pengajuan/lembar-kerja");
$testRoute("/api/v1/sla");
$testRoute("/api/v1/ulasan/kpi");


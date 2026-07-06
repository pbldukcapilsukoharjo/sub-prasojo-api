<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$testRoute = function($uri) use ($kernel, $app) {
    echo "--- Testing $uri ---\n";
    $request = Illuminate\Http\Request::create($uri, 'GET');
    
    // Bypass authentication for testing internal routing/errors
    $user = \App\Models\Monitoring\SubUser::first();
    if ($user) {
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        $app->make('auth')->guard()->setUser($user);
    }
    
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    
    $content = $response->getContent();
    if (strlen($content) > 1000) {
        echo "Content (truncated): " . substr($content, 0, 1000) . "...\n\n";
    } else {
        echo "Content: " . $content . "\n\n";
    }
};

$testRoute('/api/v1/pengajuan/lembar-kerja');
$testRoute('/api/v1/sla');
$testRoute('/api/v1/ulasan/kpi');

<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$all = \App\Models\Prasojo\Layanan::take(2)->get()->toArray();
echo "ALL:\n";
var_dump($all);

$active = \App\Models\Prasojo\Layanan::where('layanan_is_active', true)->get()->toArray();
echo "ACTIVE (true):\n";
var_dump(count($active));

$active1 = \App\Models\Prasojo\Layanan::where('layanan_is_active', 1)->get()->toArray();
echo "ACTIVE (1):\n";
var_dump(count($active1));

$activeStr = \App\Models\Prasojo\Layanan::where('layanan_is_active', '1')->get()->toArray();
echo "ACTIVE ('1'):\n";
var_dump(count($activeStr));

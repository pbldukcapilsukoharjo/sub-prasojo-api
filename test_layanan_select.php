<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$data = \App\Models\Prasojo\Layanan::select('layanan_kode as id', 'layanan_nama as name')
    ->where('layanan_is_active', true)
    ->orderBy('layanan_pos')
    ->get()
    ->toArray();
    
var_dump($data);

<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$codes = \App\Models\Prasojo\Ajuan::select('ajuan_layanan_kode')->distinct()->get()->toArray();
var_dump($codes);

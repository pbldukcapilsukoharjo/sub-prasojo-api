<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

$ajuan = App\Models\Prasojo\Ajuan::with(["pelapor", "jenisAjuan", "logStatuses"])->first();
$lembarKerja = App\Models\Prasojo\LembarKerja::with(["produk", "ajuan"])->first();
$produk = App\Models\Prasojo\Produk::with(["pelapor", "ajuan", "logStatuses"])->first();

$ajuanJson = json_encode(App\Http\Resources\Ajuan\AjuanDetailResource::make($ajuan)->resolve());
$lkJson = json_encode(App\Http\Resources\LembarKerja\LembarKerjaDetailResource::make($lembarKerja)->resolve());
$produkJson = json_encode(App\Http\Resources\Produk\ProdukDetailResource::make($produk)->resolve());

file_put_contents("test_data.json", json_encode([
    "ajuan" => json_decode($ajuanJson),
    "lembar_kerja" => json_decode($lkJson),
    "produk" => json_decode($produkJson)
], JSON_PRETTY_PRINT));


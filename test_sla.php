<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\SLAService;

try {
    $service = new SLAService();
    
    echo "--- Testing getKpi() ---\n";
    $kpi = $service->getKpi([]);
    print_r($kpi);

    echo "\n--- Testing index() ---\n";
    $index = $service->index([]);
    // Hanya print kunci utama agar tidak terlalu panjang
    echo "Rata-rata global: " . $index['rata_rata_waktu_proses'] . " jam\n";
    echo "Pencapaian: " . $index['pencapaian_sla'] . "%\n";
    echo "Jumlah Ajuan: " . $index['jumlah_ajuan'] . "\n";
    echo "Jumlah rincian layanan: " . count($index['daftar_rincian']['list']) . "\n";
    
    echo "\n--- Testing export() ---\n";
    $export = $service->export([]);
    echo "Jumlah data diekspor: " . count($export) . "\n";
    if (count($export) > 0) {
        echo "Sampel data pertama:\n";
        print_r($export[0]);
    }
    
    echo "\n[SUKSES] Kueri berjalan tanpa error SQL!\n";
} catch (\Throwable $e) {
    echo "\n[ERROR] " . $e->getMessage() . "\n";
}

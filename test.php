<?php
$service = app(App\Services\SLAService::class);
$result = $service->getLaporanSLA(["tahun" => 2025, "bulan" => 6]);
dump($result);

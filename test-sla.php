<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$statusSelesai = "'" . implode("','", App\Enums\AjuanStatus::getStatusSelesai()) . "'";
$query = App\Models\Prasojo\Ajuan::query();
$kpi = $query->select(
    \Illuminate\Support\Facades\DB::raw('COUNT(ajuan_id) as total_pengajuan'),
    \Illuminate\Support\Facades\DB::raw("AVG(CASE WHEN ajuan_status IN ($statusSelesai) THEN TIMESTAMPDIFF(MINUTE, ajuan_create_datetime, ajuan_update_datetime) ELSE NULL END) as rata_rata_sla")
)->first();

print_r($kpi->toArray());

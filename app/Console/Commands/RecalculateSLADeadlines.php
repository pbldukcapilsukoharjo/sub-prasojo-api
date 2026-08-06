<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AjuanSlaSummary;
use App\Services\SLACalculator;

class RecalculateSLADeadlines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sla:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate SLA deadlines for active ajuans when operational hours or holidays change.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting SLA deadline recalculation for active ajuans...');

        // Ambil semua ajuan yang belum selesai (waktu_selesai masih null)
        $activeAjuans = AjuanSlaSummary::whereNull('waktu_selesai')
            ->whereNotNull('waktu_mulai')
            ->get();

        $count = 0;
        foreach ($activeAjuans as $summary) {
            $targetMenit = $summary->target_sla_menit ?? $summary->target_sla_menit_aktual ?? 360;

            // Mode A
            $startTimeFullRow = \Illuminate\Support\Facades\DB::connection('mysql_prasojo')
                ->table('log_ajuan_status')
                ->where('log_ajuan_id', $summary->ajuan_id)
                ->orderBy('log_create_datetime', 'asc')
                ->first();
            $waktuMulaiModeA = $startTimeFullRow ? $startTimeFullRow->log_create_datetime : $summary->waktu_mulai;

            // Mode B (PROSES VERIFIKASI terakhir)
            $startTimeRow = \Illuminate\Support\Facades\DB::connection('mysql_prasojo')
                ->table('log_ajuan_status')
                ->where('log_ajuan_id', $summary->ajuan_id)
                ->where('log_status', 'PROSES VERIFIKASI')
                ->orderBy('log_create_datetime', 'desc')
                ->first();
            $waktuMulaiModeB = $startTimeRow ? $startTimeRow->log_create_datetime : $waktuMulaiModeA;

            $summary->target_waktu_selesai_kondisi_a = SLACalculator::calculateTargetDatetime($waktuMulaiModeA, $targetMenit);
            $summary->target_waktu_selesai_kondisi_b = SLACalculator::calculateTargetDatetime($waktuMulaiModeB, $targetMenit);
            
            $summary->save();

            // Sync custom user summaries
            $endLog = \Illuminate\Support\Facades\DB::connection('mysql_prasojo')
                ->table('log_ajuan_status')
                ->where('log_ajuan_id', $summary->ajuan_id)
                ->where('log_status', 'SELESAI DIPROSES')
                ->orderBy('log_create_datetime', 'desc')
                ->first();

            $endTime = $endLog ? $endLog->log_create_datetime : null;
            $operatorId = $endLog ? $endLog->log_admin_id : 0;

            if ($endTime) {
                \App\Services\SLAPrecalculator::syncAjuanForCustomUsers($summary->ajuan_id, (string)$endTime, (int)$operatorId);
            }

            $count++;
        }

        $this->info("Successfully recalculated SLA deadlines for {$count} active ajuans.");
    }
}

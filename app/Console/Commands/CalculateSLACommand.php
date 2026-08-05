<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\AjuanSlaSummary;
use App\Services\SLACalculator;

class CalculateSLACommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-sla';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and aggregate SLA duration for completed Ajuans';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting SLA calculation...');

        // Get all ajuan_id that are 'SELESAI DIPROSES', taking the latest row per ajuan_id
        $completedAjuans = DB::connection('mysql_prasojo')
            ->table('log_ajuan_status')
            ->where('log_status', 'SELESAI DIPROSES')
            ->whereIn('log_id', function ($query) {
                $query->select(DB::raw('MAX(log_id)'))
                      ->from('log_ajuan_status')
                      ->where('log_status', 'SELESAI DIPROSES')
                      ->groupBy('log_ajuan_id');
            })
            ->get();

        $bar = $this->output->createProgressBar(count($completedAjuans));
        $bar->start();

        $processedCount = 0;

        foreach ($completedAjuans as $ajuan) {
            $endTime = $ajuan->log_create_datetime;
            $operatorId = $ajuan->log_admin_id;

            // Target SLA snapshot (menggunakan default global)
            // Evaluasi sebenarnya dilakukan secara dinamis di SLAService berdasarkan user yang sedang login.
            $targetMenit = config('sla.default_jam', 6) * 60; 

            // Find start time Mode B (PROSES VERIFIKASI terakhir)
            $startTimeRow = DB::connection('mysql_prasojo')
                ->table('log_ajuan_status')
                ->where('log_ajuan_id', $ajuan->log_ajuan_id)
                ->where('log_status', 'PROSES VERIFIKASI')
                ->orderBy('log_create_datetime', 'desc')
                ->first();

            // Find start time Mode A (Log pertama kali ajuan dibuat)
            $startTimeFullRow = DB::connection('mysql_prasojo')
                ->table('log_ajuan_status')
                ->where('log_ajuan_id', $ajuan->log_ajuan_id)
                ->orderBy('log_create_datetime', 'asc')
                ->first();

            if (!$startTimeFullRow) {
                $bar->advance();
                continue;
            }

            $waktuMulaiModeB = $startTimeRow ? $startTimeRow->log_create_datetime : $startTimeFullRow->log_create_datetime;
            $waktuMulaiModeA = $startTimeFullRow->log_create_datetime;

            // Calculate business minutes
            $minutesB = SLACalculator::calculateMinutes($waktuMulaiModeB, $endTime);
            $minutesA = SLACalculator::calculateMinutes($waktuMulaiModeA, $endTime);

            // Calculate target datetimes
            $targetDatetimeB = SLACalculator::calculateTargetDatetime($waktuMulaiModeB, $targetMenit);
            $targetDatetimeA = SLACalculator::calculateTargetDatetime($waktuMulaiModeA, $targetMenit);

            // Save to summary table (use updateOrCreate to update existing historical data)
            AjuanSlaSummary::updateOrCreate(
                ['ajuan_id' => $ajuan->log_ajuan_id],
                [
                    'operator_user_id' => $operatorId,
                    'target_sla_menit_aktual' => $targetMenit,
                    'waktu_mulai' => $waktuMulaiModeB, // Default legacy
                    'waktu_selesai' => $endTime,
                    'durasi_sla_menit' => $minutesB, // Default legacy
                    'durasi_kondisi_a_menit' => $minutesA,
                    'durasi_kondisi_b_menit' => $minutesB,
                    'target_waktu_selesai_kondisi_a' => $targetDatetimeA,
                    'target_waktu_selesai_kondisi_b' => $targetDatetimeB,
                ]
            );

            $processedCount++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("SLA calculation completed. Processed {$processedCount} new ajuans.");
    }
}

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

        // Get all ajuan_id that are 'SELESAI DIPROSES'
        $completedAjuans = DB::connection('mysql_prasojo')
            ->table('log_ajuan_status')
            ->where('log_status', 'SELESAI DIPROSES')
            ->select('log_ajuan_id', DB::raw('MAX(log_create_datetime) as end_time'))
            ->groupBy('log_ajuan_id')
            ->get();

        $bar = $this->output->createProgressBar(count($completedAjuans));
        $bar->start();

        $processedCount = 0;

        foreach ($completedAjuans as $ajuan) {
            // Check if already calculated
            $exists = AjuanSlaSummary::where('ajuan_id', $ajuan->log_ajuan_id)->exists();
            if ($exists) {
                $bar->advance();
                continue;
            }

            // Find start time
            $startTimeRow = DB::connection('mysql_prasojo')
                ->table('log_ajuan_status')
                ->where('log_ajuan_id', $ajuan->log_ajuan_id)
                ->where('log_status', 'PROSES VERIFIKASI')
                ->orderBy('log_create_datetime', 'desc')
                ->first();

            if (!$startTimeRow) {
                $bar->advance();
                continue;
            }

            $startTime = $startTimeRow->log_create_datetime;
            $endTime = $ajuan->end_time;

            // Calculate business minutes
            $minutes = SLACalculator::calculateMinutes($startTime, $endTime);

            // Save to summary table
            AjuanSlaSummary::create([
                'ajuan_id' => $ajuan->log_ajuan_id,
                'waktu_mulai' => $startTime,
                'waktu_selesai' => $endTime,
                'durasi_sla_menit' => $minutes,
            ]);

            $processedCount++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("SLA calculation completed. Processed {$processedCount} new ajuans.");
    }
}

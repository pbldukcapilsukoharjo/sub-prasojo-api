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
            ->whereNotNull('target_sla_menit_aktual')
            ->get();

        $count = 0;
        foreach ($activeAjuans as $summary) {
            $targetDatetime = SLACalculator::calculateTargetDatetime($summary->waktu_mulai, $summary->target_sla_menit_aktual);
            
            // Kita asumsikan saat ini target waktu kondisi A (End-to-End) disamakan dengan target keseluruhan.
            // Jika ada logika berbeda untuk kondisi B, dapat ditambahkan di sini.
            $summary->target_waktu_selesai_kondisi_a = $targetDatetime;
            $summary->target_waktu_selesai_kondisi_b = $targetDatetime; // Bisa disesuaikan logicnya nanti
            
            $summary->save();
            $count++;
        }

        $this->info("Successfully recalculated SLA deadlines for {$count} active ajuans.");
    }
}

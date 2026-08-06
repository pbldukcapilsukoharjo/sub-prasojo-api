<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Monitoring\SubUser;
use App\Models\UserAjuanSlaSummary;
use App\Models\AjuanSlaSummary;
use Illuminate\Support\Facades\DB;
use App\Services\SLACalculator;

class SLAPrecalculator
{
    /**
     * Recalculate SLA summaries for a specific user's custom settings.
     *
     * @param SubUser $user
     * @return void
     */
    public static function recalculateForUser(SubUser $user): void
    {
        $startStatus = $user->sla_start_status;
        $endStatus = $user->sla_end_status;

        // Jika setting kosong, hapus ringkasan kustom user ini
        if (empty($startStatus) && empty($endStatus)) {
            UserAjuanSlaSummary::where('user_id', $user->id)->delete();
            return;
        }

        // Fallback default
        $startStatus = $startStatus ?: '[FIRST_LOG]';
        $endStatus = $endStatus ?: 'SELESAI DIPROSES';

        // 1. Ambal semua ajuan yang telah mencapai endStatus
        $completedAjuans = DB::connection('mysql_prasojo')
            ->table('log_ajuan_status')
            ->where('log_status', $endStatus)
            ->whereIn('log_id', function ($query) use ($endStatus) {
                $query->select(DB::raw('MAX(log_id)'))
                      ->from('log_ajuan_status')
                      ->where('log_status', $endStatus)
                      ->groupBy('log_ajuan_id');
            })
            ->get();

        if ($completedAjuans->isEmpty()) {
            UserAjuanSlaSummary::where('user_id', $user->id)->delete();
            return;
        }

        // 2. Ambil log pertama untuk semua ajuan (fallback / [FIRST_LOG])
        $firstLogs = DB::connection('mysql_prasojo')
            ->table('log_ajuan_status')
            ->whereIn('log_id', function ($query) {
                $query->select(DB::raw('MIN(log_id)'))
                      ->from('log_ajuan_status')
                      ->groupBy('log_ajuan_id');
            })
            ->get()
            ->keyBy('log_ajuan_id');

        // 3. Ambil log start status jika bukan [FIRST_LOG]
        $startLogs = collect();
        if ($startStatus !== '[FIRST_LOG]') {
            $startLogs = DB::connection('mysql_prasojo')
                ->table('log_ajuan_status')
                ->where('log_status', $startStatus)
                ->get()
                ->groupBy('log_ajuan_id');
        }

        // 4. Ambil peta target SLA per ajuan yang sudah ada
        $targetSlaMap = DB::table('ajuan_sla_summaries')
            ->pluck('target_sla_menit', 'ajuan_id')
            ->toArray();

        $records = [];
        $now = now();

        foreach ($completedAjuans as $ajuan) {
            $ajuanId = $ajuan->log_ajuan_id;
            $endTime = $ajuan->log_create_datetime;
            $operatorId = $ajuan->log_admin_id;

            // Cari target SLA ajuan ini (default 6 jam = 360 menit)
            $targetSlaMenit = $targetSlaMap[$ajuanId] ?? 360;

            // Tentukan waktu mulai
            $waktuMulai = null;
            if ($startStatus === '[FIRST_LOG]') {
                $waktuMulai = isset($firstLogs[$ajuanId]) ? $firstLogs[$ajuanId]->log_create_datetime : null;
            } else {
                $logGroup = $startLogs->get($ajuanId);
                if ($logGroup && $logGroup->isNotEmpty()) {
                    if ($startStatus === 'PROSES VERIFIKASI') {
                        // Ambil yang paling baru
                        $waktuMulai = $logGroup->sortByDesc('log_create_datetime')->first()->log_create_datetime;
                    } else {
                        // Ambil yang paling lama / pertama kali masuk status tersebut
                        $waktuMulai = $logGroup->sortBy('log_create_datetime')->first()->log_create_datetime;
                    }
                }
            }

            // Hitung durasi dan target waktu selesai
            $minutes = null;
            $targetDatetime = null;

            if ($waktuMulai && $endTime) {
                $minutes = SLACalculator::calculateMinutes($waktuMulai, $endTime);
                $targetDatetime = SLACalculator::calculateTargetDatetime($waktuMulai, $targetSlaMenit);
            }

            $records[] = [
                'user_id' => $user->id,
                'ajuan_id' => $ajuanId,
                'waktu_mulai' => $waktuMulai,
                'waktu_selesai' => $endTime,
                'durasi_sla_menit' => $minutes,
                'target_waktu_selesai' => $targetDatetime,
                'operator_user_id' => $operatorId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Hapus data lama user ini, lalu insert sekaligus (bulk insert) untuk performa optimal
        DB::transaction(function () use ($user, $records) {
            UserAjuanSlaSummary::where('user_id', $user->id)->delete();
            
            // Insert in chunks of 500 rows to prevent DB packet size limits
            foreach (array_chunk($records, 500) as $chunk) {
                UserAjuanSlaSummary::insert($chunk);
            }
        });
    }

    /**
     * Sync custom summaries for a specific ajuan when it is updated/completed.
     *
     * @param int|string $ajuanId
     * @param string $endTime
     * @param int $operatorId
     * @return void
     */
    public static function syncAjuanForCustomUsers(int|string $ajuanId, string $endTime, int $operatorId): void
    {
        // Cari semua sub_users yang memiliki konfigurasi kustom
        $customUsers = SubUser::whereNotNull('sla_start_status')
            ->orWhereNotNull('sla_end_status')
            ->get();

        if ($customUsers->isEmpty()) {
            return;
        }

        // Ambil semua log status untuk ajuan ini
        $logs = DB::connection('mysql_prasojo')
            ->table('log_ajuan_status')
            ->where('log_ajuan_id', $ajuanId)
            ->get();

        if ($logs->isEmpty()) {
            return;
        }

        $firstLog = $logs->sortBy('log_create_datetime')->first();
        $targetSlaSummary = AjuanSlaSummary::where('ajuan_id', $ajuanId)->first();
        $targetSlaMenit = $targetSlaSummary ? ($targetSlaSummary->target_sla_menit ?? 360) : 360;

        foreach ($customUsers as $user) {
            $startStatus = $user->sla_start_status ?: '[FIRST_LOG]';
            $endStatus = $user->sla_end_status ?: 'SELESAI DIPROSES';

            // Cek apakah ajuan ini sudah mencapai endStatus kustom user
            $endLogs = $logs->where('log_status', $endStatus);
            if ($endLogs->isEmpty()) {
                // Jika belum mencapai endStatus, hapus record custom summary jika ada
                UserAjuanSlaSummary::where('user_id', $user->id)
                    ->where('ajuan_id', $ajuanId)
                    ->delete();
                continue;
            }

            $userEndTime = $endLogs->sortByDesc('log_create_datetime')->first()->log_create_datetime;
            $userOperatorId = $endLogs->sortByDesc('log_create_datetime')->first()->log_admin_id;

            // Cari waktu mulai
            $waktuMulai = null;
            if ($startStatus === '[FIRST_LOG]') {
                $waktuMulai = $firstLog ? $firstLog->log_create_datetime : null;
            } else {
                $startLogs = $logs->where('log_status', $startStatus);
                if ($startLogs->isNotEmpty()) {
                    if ($startStatus === 'PROSES VERIFIKASI') {
                        $waktuMulai = $startLogs->sortByDesc('log_create_datetime')->first()->log_create_datetime;
                    } else {
                        $waktuMulai = $startLogs->sortBy('log_create_datetime')->first()->log_create_datetime;
                    }
                }
            }

            $minutes = null;
            $targetDatetime = null;

            if ($waktuMulai && $userEndTime) {
                $minutes = SLACalculator::calculateMinutes($waktuMulai, $userEndTime);
                $targetDatetime = SLACalculator::calculateTargetDatetime($waktuMulai, $targetSlaMenit);
            }

            UserAjuanSlaSummary::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'ajuan_id' => $ajuanId,
                ],
                [
                    'waktu_mulai' => $waktuMulai,
                    'waktu_selesai' => $userEndTime,
                    'durasi_sla_menit' => $minutes,
                    'target_waktu_selesai' => $targetDatetime,
                    'operator_user_id' => $userOperatorId,
                ]
            );
        }
    }
}

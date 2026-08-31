<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Monitoring\SubUser;
use App\Models\UserAjuanSlaSummary;
use App\Models\AjuanSlaSummary;
use Illuminate\Support\Facades\DB;
use App\Services\SLACalculator;
use Carbon\Carbon;

class SLAPrecalculator
{
    /**
     * Recalculate SLA summaries for a specific user's custom settings.
     *
     * Optimized version:
     * - Single cross-database SQL query (replaces 3-4 separate queries)
     * - Pre-loaded holidays & business hours (replaces per-ajuan DB calls in SLACalculator)
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

        $prasojoDb = config('database.connections.mysql_prasojo.database');
        $dashboardDb = config('database.connections.mysql.database');

        // ── Build start-time subquery berdasarkan startStatus ──
        if ($startStatus === '[FIRST_LOG]') {
            $startSubquery = "
                LEFT JOIN (
                    SELECT log_ajuan_id, MIN(log_create_datetime) AS waktu_mulai
                    FROM `{$prasojoDb}`.log_ajuan_status
                    GROUP BY log_ajuan_id
                ) start_log ON start_log.log_ajuan_id = end_log.log_ajuan_id
            ";
            $startParams = [];
        } else {
            $aggFunc = $startStatus === 'PROSES VERIFIKASI' ? 'MAX' : 'MIN';
            $startSubquery = "
                LEFT JOIN (
                    SELECT log_ajuan_id, {$aggFunc}(log_create_datetime) AS waktu_mulai
                    FROM `{$prasojoDb}`.log_ajuan_status
                    WHERE log_status = ?
                    GROUP BY log_ajuan_id
                ) start_log ON start_log.log_ajuan_id = end_log.log_ajuan_id
            ";
            $startParams = [$startStatus];
        }

        // ── Single cross-database query ──
        $sql = "
            SELECT
                end_log.log_ajuan_id   AS ajuan_id,
                start_log.waktu_mulai,
                end_log.waktu_selesai,
                end_log.operator_id,
                COALESCE(target.target_sla_menit, 360) AS target_sla_menit
            FROM (
                SELECT las_end.log_ajuan_id,
                       las_end.log_create_datetime AS waktu_selesai,
                       las_end.log_admin_id        AS operator_id
                FROM `{$prasojoDb}`.log_ajuan_status las_end
                INNER JOIN (
                    SELECT log_ajuan_id, MAX(log_id) AS max_id
                    FROM `{$prasojoDb}`.log_ajuan_status
                    WHERE log_status = ?
                    GROUP BY log_ajuan_id
                ) las_end_max ON las_end.log_id = las_end_max.max_id
            ) end_log
            {$startSubquery}
            LEFT JOIN `{$dashboardDb}`.ajuan_sla_summaries target
                ON target.ajuan_id = end_log.log_ajuan_id
        ";

        $params = array_merge([$endStatus], $startParams);
        $results = DB::connection('mysql_prasojo')->select($sql, $params);

        if (empty($results)) {
            UserAjuanSlaSummary::where('user_id', $user->id)->delete();
            return;
        }

        // ── Pre-load holidays & business hours SEKALI untuk semua kalkulasi ──
        $validResults = collect($results)->filter(fn($r) => $r->waktu_mulai && $r->waktu_selesai);

        $sharedData = null;
        if ($validResults->isNotEmpty()) {
            $minDate = Carbon::parse($validResults->min('waktu_mulai'));
            // Buffer +30 hari untuk kalkulasi target_waktu_selesai yang bisa melampaui data range
            $maxDate = Carbon::parse($validResults->max('waktu_selesai'))->addDays(30);
            $sharedData = SLACalculator::loadSharedData($minDate, $maxDate);
        }

        // ── Hitung SLA per ajuan menggunakan cached data ──
        $records = [];
        $now = now();

        foreach ($results as $row) {
            $minutes = null;
            $targetDatetime = null;

            if ($row->waktu_mulai && $row->waktu_selesai && $sharedData) {
                $minutes = SLACalculator::calculateMinutesWithCache(
                    $row->waktu_mulai,
                    $row->waktu_selesai,
                    $sharedData['masterJam'],
                    $sharedData['holidays']
                );
                $targetDatetime = SLACalculator::calculateTargetDatetimeWithCache(
                    $row->waktu_mulai,
                    (int) $row->target_sla_menit,
                    $sharedData['masterJam'],
                    $sharedData['holidays']
                );
            }

            $records[] = [
                'user_id'              => $user->id,
                'ajuan_id'             => $row->ajuan_id,
                'waktu_mulai'          => $row->waktu_mulai,
                'waktu_selesai'        => $row->waktu_selesai,
                'durasi_sla_menit'     => $minutes,
                'target_waktu_selesai' => $targetDatetime,
                'operator_user_id'     => $row->operator_id,
                'created_at'           => $now,
                'updated_at'           => $now,
            ];
        }

        // ── Hapus data lama, bulk insert baru ──
        DB::transaction(function () use ($user, $records) {
            UserAjuanSlaSummary::where('user_id', $user->id)->delete();

            foreach (array_chunk($records, 500) as $chunk) {
                UserAjuanSlaSummary::insert($chunk);
            }
        });
    }

    /**
     * Sync custom summaries for a specific ajuan when it is updated/completed.
     *
     * Optimized: pre-load holidays & business hours ONCE before iterating users.
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

        // ── Pre-load holidays & business hours SEKALI untuk semua users ──
        $minDate = Carbon::parse($logs->min('log_create_datetime'));
        $maxDate = Carbon::parse($logs->max('log_create_datetime'))->addDays(30);
        $sharedData = SLACalculator::loadSharedData($minDate, $maxDate);

        foreach ($customUsers as $user) {
            $startStatus = $user->sla_start_status ?: '[FIRST_LOG]';
            $userEndStatus = $user->sla_end_status ?: 'SELESAI DIPROSES';

            // Cek apakah ajuan ini sudah mencapai endStatus kustom user
            $endLogs = $logs->where('log_status', $userEndStatus);
            if ($endLogs->isEmpty()) {
                // Jika belum mencapai endStatus, hapus record custom summary jika ada
                UserAjuanSlaSummary::where('user_id', $user->id)
                    ->where('ajuan_id', $ajuanId)
                    ->delete();
                continue;
            }

            $latestEndLog = $endLogs->sortByDesc('log_create_datetime')->first();
            $userEndTime = $latestEndLog->log_create_datetime;
            $userOperatorId = $latestEndLog->log_admin_id;

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
                $minutes = SLACalculator::calculateMinutesWithCache(
                    $waktuMulai,
                    $userEndTime,
                    $sharedData['masterJam'],
                    $sharedData['holidays']
                );
                $targetDatetime = SLACalculator::calculateTargetDatetimeWithCache(
                    $waktuMulai,
                    $targetSlaMenit,
                    $sharedData['masterJam'],
                    $sharedData['holidays']
                );
            }

            UserAjuanSlaSummary::updateOrCreate(
                [
                    'user_id'  => $user->id,
                    'ajuan_id' => $ajuanId,
                ],
                [
                    'waktu_mulai'          => $waktuMulai,
                    'waktu_selesai'        => $userEndTime,
                    'durasi_sla_menit'     => $minutes,
                    'target_waktu_selesai' => $targetDatetime,
                    'operator_user_id'     => $userOperatorId,
                ]
            );
        }
    }
}

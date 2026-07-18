<?php

namespace App\Services;

use App\Models\MasterLiburNasional;
use App\Models\MasterJamOperasional;
use Carbon\Carbon;
use Spatie\Holidays\Holidays;

class SLACalculator
{
    /**
     * Calculate SLA duration in minutes between two timestamps.
     * Business Hours are fetched dynamically from MasterJamOperasional.
     * 
     * @param string|Carbon $startTime
     * @param string|Carbon $endTime
     * @return int
     */
    public static function calculateMinutes($startTime, $endTime): int
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        
        if ($end->lessThan($start)) {
            return 0;
        }

        $totalMinutes = 0;
        $current = $start->copy();

        // Get all holidays in the year(s) range
        $years = range($start->year, $end->year);
        $holidays = [];
        foreach ($years as $year) {
            $spatieHolidays = Holidays::for('id', $year)->get();
            foreach ($spatieHolidays as $holiday) {
                // $holiday is an object of Spatie\Holidays\Holiday
                $holidays[] = Carbon::parse($holiday->date)->format('Y-m-d');
            }
        }
        
        // Merge with MasterLiburNasional
        $dbHolidays = MasterLiburNasional::whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                                         ->pluck('tanggal')
                                         ->map(function($date) {
                                             return Carbon::parse($date)->format('Y-m-d');
                                         })
                                         ->toArray();
                                         
        $allHolidays = array_unique(array_merge($holidays, $dbHolidays));

        // Ambil master jam operasional
        $masterJam = MasterJamOperasional::all()->keyBy('hari_kode');

        while ($current->lessThan($end)) {
            $hariKode = $current->dayOfWeekIso; // 1 = Senin, 7 = Minggu
            $jamOp = $masterJam->get($hariKode);
            
            $isLiburNasional = in_array($current->format('Y-m-d'), $allHolidays);
            $isLiburHari = $jamOp ? $jamOp->is_libur : $current->isWeekend();

            if (!$isLiburNasional && !$isLiburHari && $jamOp && $jamOp->jam_buka && $jamOp->jam_tutup) {
                // Determine business hours for current day
                $partsBuka = explode(':', $jamOp->jam_buka);
                $partsTutup = explode(':', $jamOp->jam_tutup);
                
                $businessStart = $current->copy()->setTime((int)$partsBuka[0], (int)$partsBuka[1], 0);
                $businessEnd = $current->copy()->setTime((int)$partsTutup[0], (int)$partsTutup[1], 0);

                if ($current->isSameDay($start) && $current->isSameDay($end)) {
                    // Both start and end on the same day
                    $calcStart = $start->copy()->max($businessStart);
                    $calcEnd = $end->copy()->min($businessEnd);
                    if ($calcStart->lessThan($calcEnd)) {
                        $totalMinutes += $calcStart->diffInMinutes($calcEnd);
                    }
                } elseif ($current->isSameDay($start)) {
                    // It's the start day, go from $start (or business start) to business end
                    $calcStart = $start->copy()->max($businessStart);
                    if ($calcStart->lessThan($businessEnd)) {
                        $totalMinutes += $calcStart->diffInMinutes($businessEnd);
                    }
                } elseif ($current->isSameDay($end)) {
                    // It's the end day, go from business start to $end (or business end)
                    $calcEnd = $end->copy()->min($businessEnd);
                    if ($businessStart->lessThan($calcEnd)) {
                        $totalMinutes += $businessStart->diffInMinutes($calcEnd);
                    }
                } else {
                    // A full business day in between
                    $totalMinutes += $businessStart->diffInMinutes($businessEnd);
                }
            }

            // Move to the next day
            $current->addDay()->startOfDay();
        }

        return $totalMinutes;
    }

    /**
     * Calculate target datetime given a start time and target SLA duration in minutes.
     * Skips non-business hours and holidays dynamically.
     * 
     * @param string|Carbon $startTime
     * @param int $targetMinutes
     * @return Carbon|null
     */
    public static function calculateTargetDatetime($startTime, int $targetMinutes): ?Carbon
    {
        if ($targetMinutes <= 0) {
            return Carbon::parse($startTime);
        }

        $current = Carbon::parse($startTime);
        $masterJam = MasterJamOperasional::all()->keyBy('hari_kode');

        $currentYear = null;
        $allHolidays = [];

        while ($targetMinutes > 0) {
            if ($currentYear !== $current->year) {
                $currentYear = $current->year;
                $spatieHolidays = Holidays::for('id', $currentYear)->get();
                $holidays = [];
                foreach ($spatieHolidays as $holiday) {
                    $holidays[] = Carbon::parse($holiday->date)->format('Y-m-d');
                }
                
                $dbHolidays = MasterLiburNasional::whereYear('tanggal', $currentYear)
                    ->pluck('tanggal')
                    ->map(function($date) {
                        return Carbon::parse($date)->format('Y-m-d');
                    })
                    ->toArray();

                $allHolidays = array_unique(array_merge($allHolidays, $holidays, $dbHolidays));
            }

            $hariKode = $current->dayOfWeekIso;
            $jamOp = $masterJam->get($hariKode);
            
            $isLiburNasional = in_array($current->format('Y-m-d'), $allHolidays);
            $isLiburHari = $jamOp ? $jamOp->is_libur : $current->isWeekend();

            if (!$isLiburNasional && !$isLiburHari && $jamOp && $jamOp->jam_buka && $jamOp->jam_tutup) {
                $partsBuka = explode(':', $jamOp->jam_buka);
                $partsTutup = explode(':', $jamOp->jam_tutup);
                
                $businessStart = $current->copy()->setTime((int)$partsBuka[0], (int)$partsBuka[1], 0);
                $businessEnd = $current->copy()->setTime((int)$partsTutup[0], (int)$partsTutup[1], 0);

                if ($current->lessThan($businessStart)) {
                    $current = $businessStart->copy();
                }

                if ($current->lessThan($businessEnd)) {
                    $availableMinutes = $current->diffInMinutes($businessEnd);

                    if ($availableMinutes >= $targetMinutes) {
                        return $current->copy()->addMinutes($targetMinutes);
                    } else {
                        $targetMinutes -= $availableMinutes;
                    }
                }
            }

            $current->addDay()->startOfDay();
        }

        return $current;
    }
}

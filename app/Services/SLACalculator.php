<?php

namespace App\Services;

use App\Models\MasterLiburNasional;
use Carbon\Carbon;
use Spatie\Holidays\Holidays;

class SLACalculator
{
    /**
     * Calculate SLA duration in minutes between two timestamps.
     * Business Hours:
     * - Monday-Thursday: 08:00 - 15:00
     * - Friday: 08:00 - 13:00
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

        while ($current->lessThan($end)) {
            $isWeekend = $current->isWeekend();
            $isHoliday = in_array($current->format('Y-m-d'), $allHolidays);

            if (!$isWeekend && (!$isHoliday)) {
                // Determine business hours for current day
                $startHour = 8;
                $endHour = $current->isFriday() ? 13 : 15;
                
                $businessStart = $current->copy()->setTime($startHour, 0, 0);
                $businessEnd = $current->copy()->setTime($endHour, 0, 0);

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
}

<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SLACalculator;
use App\Models\MasterLiburNasional;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SLACalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\SlaConfigSeeder::class);
    }

    public function test_normal_monday_to_thursday()
    {
        // Monday 08:00 - 15:00 is 7 hours = 420 mins
        $minutes = SLACalculator::calculateMinutes('2026-07-20 08:00:00', '2026-07-20 15:00:00');
        $this->assertEquals(420, $minutes);
    }

    public function test_normal_friday()
    {
        // Friday 08:00 - 13:00 is 5 hours = 300 mins
        $minutes = SLACalculator::calculateMinutes('2026-07-17 08:00:00', '2026-07-17 13:00:00');
        $this->assertEquals(300, $minutes);
    }

    public function test_cross_weekend()
    {
        // Friday 12:00 to Monday 10:00
        // Friday: 12:00-13:00 (1 hour = 60 mins)
        // Monday: 08:00-10:00 (2 hours = 120 mins)
        // Total: 180 mins
        $minutes = SLACalculator::calculateMinutes('2026-07-17 12:00:00', '2026-07-20 10:00:00');
        $this->assertEquals(180, $minutes);
    }

    public function test_spatie_holiday()
    {
        // 17 August 2026 is Independence Day (Monday)
        // Friday 14 Aug 12:00 to Tuesday 18 Aug 10:00
        // Friday: 12:00-13:00 = 60 mins
        // Monday: Holiday (0 mins)
        // Tuesday: 08:00-10:00 = 120 mins
        // Total = 180 mins
        $minutes = SLACalculator::calculateMinutes('2026-08-14 12:00:00', '2026-08-18 10:00:00');
        $this->assertEquals(180, $minutes);
    }

    public function test_master_libur_nasional()
    {
        // Tuesday 21 July 2026 is normally a work day.
        // Let's add it to MasterLiburNasional.
        MasterLiburNasional::create([
            'tanggal' => '2026-07-21',
            'keterangan' => 'Cuti Bersama'
        ]);

        // Monday 20 July 14:00 to Wednesday 22 July 10:00
        // Monday: 14:00-15:00 = 60 mins
        // Tuesday: Holiday (0 mins)
        // Wednesday: 08:00-10:00 = 120 mins
        // Total: 180 mins
        $minutes = SLACalculator::calculateMinutes('2026-07-20 14:00:00', '2026-07-22 10:00:00');
        $this->assertEquals(180, $minutes);
    }
}

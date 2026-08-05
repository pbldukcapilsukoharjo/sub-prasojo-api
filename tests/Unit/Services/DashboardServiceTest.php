<?php

namespace Tests\Unit\Services;

use App\Filters\DashboardFilter;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    public function test_get_kpi_uses_cache()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn([
                'total_pengajuan' => 10,
            ]);

        $filter = new DashboardFilter([]);
        $service = new DashboardService();

        $result = $service->getKpi($filter);

        $this->assertEquals(10, $result['total_pengajuan']);
    }

    public function test_get_chart_trend_uses_cache()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn([
                ['tanggal' => '2023-01-01', 'total_ajuan' => 5, 'selesai' => 3]
            ]);

        $filter = new DashboardFilter([]);
        $service = new DashboardService();

        $result = $service->getChartTrend($filter);

        $this->assertCount(1, $result);
        $this->assertEquals('2023-01-01', $result[0]['tanggal']);
    }

    public function test_get_top_wilayah_uses_cache()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn([
                ['id_kecamatan' => '123', 'nama_kecamatan' => 'Kecamatan Test', 'total' => 10]
            ]);

        $filter = new DashboardFilter([]);
        $service = new DashboardService();

        $result = $service->getTopWilayah($filter);

        $this->assertCount(1, $result);
        $this->assertEquals('Kecamatan Test', $result[0]['nama_kecamatan']);
    }
}

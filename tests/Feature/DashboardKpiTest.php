<?php

namespace Tests\Feature;

use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class DashboardKpiTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_unauthenticated_user_cannot_access_kpi()
    {
        $response = $this->getJson('/api/v1/dashboard/kpi');
        $response->assertStatus(401);
    }

    public function test_kpi_endpoint_returns_valid_structure()
    {
        $this->authenticateWithPaseto();

        $mockService = Mockery::mock(DashboardService::class);
        $mockService->shouldReceive('getKpi')->once()->andReturn([
            'total_pengajuan' => 100,
            'total_pengajuan_trend_persen' => 10,
            'total_selesai' => 80,
            'total_selesai_trend_persen' => 5,
            'total_ditolak' => 5,
            'total_ditolak_trend_persen' => 0,
            'rata_rata_sla_jam' => 24,
            'rata_rata_sla_trend_persen' => -2,
            'rata_rata_sla_text' => '24 Jam 0 Menit',
        ]);
        
        $this->app->instance(DashboardService::class, $mockService);

        $response = $this->getJson('/api/v1/dashboard/kpi');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'code',
                     'status',
                     'message',
                     'data' => [
                         'total_pengajuan',
                         'total_pengajuan_trend_persen',
                         'total_selesai',
                         'total_selesai_trend_persen',
                         'total_ditolak',
                         'total_ditolak_trend_persen',
                         'rata_rata_sla_jam',
                         'rata_rata_sla_trend_persen',
                         'rata_rata_sla_text',
                     ]
                 ]);
    }

    public function test_chart_trend_endpoint_returns_valid_structure()
    {
        $this->authenticateWithPaseto();

        $mockService = Mockery::mock(DashboardService::class);
        $mockService->shouldReceive('getChartTrend')->once()->andReturn([
            [
                'tanggal' => '2023-01-01',
                'total_ajuan' => 10,
                'selesai' => 5
            ]
        ]);
        
        $this->app->instance(DashboardService::class, $mockService);

        $response = $this->getJson('/api/v1/dashboard/chart-trend');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'code',
                     'status',
                     'message',
                     'data' => [
                         '*' => [
                             'tanggal',
                             'total_ajuan',
                             'selesai'
                         ]
                     ]
                 ]);
    }

    public function test_top_wilayah_endpoint_returns_valid_structure()
    {
        $this->authenticateWithPaseto();

        $mockService = Mockery::mock(DashboardService::class);
        $mockService->shouldReceive('getTopWilayah')->once()->andReturn([
            [
                'id_kecamatan' => '331101',
                'nama_kecamatan' => 'Kecamatan A',
                'total' => 50
            ]
        ]);
        
        $this->app->instance(DashboardService::class, $mockService);

        $response = $this->getJson('/api/v1/dashboard/top-wilayah');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'code',
                     'status',
                     'message',
                     'data' => [
                         '*' => [
                             'id_kecamatan',
                             'nama_kecamatan',
                             'total'
                         ]
                     ]
                 ]);
    }
}

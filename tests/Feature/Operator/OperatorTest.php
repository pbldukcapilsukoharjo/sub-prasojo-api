<?php

namespace Tests\Feature\Operator;

use App\Services\OperatorService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class OperatorTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_kpi_global_endpoint()
    {
        $mockService = Mockery::mock(OperatorService::class);
        $mockService->shouldReceive('getKpiGlobal')->once()->andReturn([
            'total_ajuan' => 100,
            'total_selesai' => 80,
            'tingkat_selesai' => 80.0,
            'rata_rata_durasi' => 5.5,
        ]);

        $this->app->instance(OperatorService::class, $mockService);

        $response = $this->getJson('/api/v1/operator/kpi-global');
        
        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'code' => 200,
                     'message' => 'Berhasil mengambil KPI global operator',
                     'data' => [
                         'total_ajuan' => 100,
                         'total_selesai' => 80,
                         'tingkat_selesai' => 80.0,
                         'rata_rata_durasi' => 5.5,
                     ]
                 ]);
    }

    public function test_peringkat_endpoint()
    {
        $mockService = Mockery::mock(OperatorService::class);
        
        $items = collect([
            [
                'id' => 1,
                'peringkat' => 1,
                'operator' => 'Test Operator',
                'desa' => 'Desa A',
                'kecamatan' => 'Kecamatan B',
                'jumlah_ajuan' => 50,
            ]
        ]);
        $paginator = new LengthAwarePaginator($items, 1, 10, 1);
        
        $mockService->shouldReceive('getRanking')->once()->andReturn($paginator);

        $this->app->instance(OperatorService::class, $mockService);

        $response = $this->getJson('/api/v1/operator/peringkat');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'code',
                     'message',
                     'data' => [
                         'list' => [
                             '*' => [
                                 'id',
                                 'peringkat',
                                 'operator',
                                 'desa',
                                 'kecamatan',
                                 'jumlah_ajuan',
                             ]
                         ],
                         'meta' => [
                             'page',
                             'per_page',
                             'total',
                             'total_page',
                         ]
                     ]
                 ]);
    }

    public function test_kpi_detail_endpoint()
    {
        $mockService = Mockery::mock(OperatorService::class);
        $mockService->shouldReceive('getDetailKpi')->once()->with(1, Mockery::any())->andReturn([
            'id' => 1,
            'nama' => 'Test Operator',
            'total_ajuan' => 50,
            'total_selesai' => 45,
            'tingkat_selesai' => 90,
            'layanan_perbulan' => [
                'Jan' => 10, 'Feb' => 20, 'Mar' => 0, 'Apr' => 0,
                'Mei' => 0, 'Jun' => 0, 'Jul' => 0, 'Agu' => 0,
                'Sep' => 0, 'Okt' => 0, 'Nov' => 0, 'Des' => 0,
            ]
        ]);

        $this->app->instance(OperatorService::class, $mockService);

        $response = $this->getJson('/api/v1/operator/1/kpi');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'code',
                     'message',
                     'data' => [
                         'id',
                         'nama',
                         'total_ajuan',
                         'total_selesai',
                         'tingkat_selesai',
                         'layanan_perbulan',
                     ]
                 ]);
    }

    public function test_riwayat_endpoint()
    {
        $mockService = Mockery::mock(OperatorService::class);
        
        $items = collect([
            [
                'id' => 100,
                'no_regis' => 'REG-123',
                'pemohon' => 'Budi',
                'kode_ajuan' => 'KTP',
                'desa' => 'Desa C',
                'tanggal' => '25-06-2026',
                'waktu' => '10:00',
                'status' => 'Selesai'
            ]
        ]);
        $paginator = new LengthAwarePaginator($items, 1, 10, 1);
        
        $mockService->shouldReceive('getRiwayat')->once()->with(1, Mockery::any(), 10)->andReturn($paginator);

        $this->app->instance(OperatorService::class, $mockService);

        $response = $this->getJson('/api/v1/operator/1/riwayat');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'code',
                     'message',
                     'data' => [
                         'list' => [
                             '*' => [
                                 'id',
                                 'no_regis',
                                 'pemohon',
                                 'kode_ajuan',
                                 'desa',
                                 'tanggal',
                                 'waktu',
                                 'status',
                             ]
                         ],
                         'meta' => [
                             'page',
                             'per_page',
                             'total',
                             'total_page',
                         ]
                     ]
                 ]);
    }

    public function test_kpi_detail_not_found()
    {
        $mockService = Mockery::mock(OperatorService::class);
        $mockService->shouldReceive('getDetailKpi')->once()->with(99999, Mockery::any())->andThrow(new ModelNotFoundException());

        $this->app->instance(OperatorService::class, $mockService);

        $response = $this->getJson('/api/v1/operator/99999/kpi');
        $response->assertStatus(404);
    }
}

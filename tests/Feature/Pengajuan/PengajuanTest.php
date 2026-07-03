<?php

namespace Tests\Feature\Pengajuan;

use App\Services\PengajuanService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PengajuanTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_lembar_kerja()
    {
        $this->authenticateWithPaseto();

        $mockService = Mockery::mock(PengajuanService::class);
        
        $items = collect([
            [
                'id' => 123,
                'no_reg' => 'REG-20240101-001',
                'layanan' => 'KTP-el',
                'kecamatan' => 'Klojen',
                'pelapor' => 'Test User',
                'status' => 'MENUNGGU',
                'created_at' => '2024-01-01 08:00:00',
            ]
        ]);
        
        $paginator = new LengthAwarePaginator($items, 1, 10, 1);
        
        $mockService->shouldReceive('getLembarKerjaList')->once()->andReturn([
            'paginator' => $paginator,
            'chart_status' => collect([['label' => 'MENUNGGU', 'value' => 1]]),
            'chart_layanan' => collect([['label' => 'KTP-el', 'value' => 1]])
        ]);
        
        $this->app->instance(PengajuanService::class, $mockService);

        $response = $this->getJson('/api/v1/pengajuan/lembar-kerja');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'code',
                     'message',
                     'data' => [
                         '*' => [
                             'id',
                             'no_reg',
                             'layanan',
                             'kecamatan',
                             'pelapor',
                             'status',
                             'created_at',
                         ]
                     ],
                     'meta' => [
                         'page',
                         'per_page',
                         'total',
                         'total_page',
                     ],
                     'chart_status',
                     'chart_layanan'
                 ]);
    }

    public function test_get_ajuan()
    {
        $this->authenticateWithPaseto();

        $mockService = Mockery::mock(PengajuanService::class);
        
        $items = collect([
            [
                'id' => 123,
                'no_reg' => 'REG-20240101-001',
                'layanan' => 'KTP-el',
                'kecamatan' => 'Klojen',
                'pelapor' => 'Test User (Online)',
                'status' => 'MENUNGGU',
                'created_at' => '2024-01-01 08:00:00',
            ]
        ]);
        
        $paginator = new LengthAwarePaginator($items, 1, 10, 1);
        
        $mockService->shouldReceive('getAjuanList')->once()->andReturn($paginator);
        
        $this->app->instance(PengajuanService::class, $mockService);

        $response = $this->getJson('/api/v1/pengajuan/ajuan');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'code',
                     'message',
                     'data' => [
                         '*' => [
                             'id',
                             'no_reg',
                             'layanan',
                             'kecamatan',
                             'pelapor',
                             'status',
                             'created_at',
                         ]
                     ],
                     'meta' => [
                         'page',
                         'per_page',
                         'total',
                         'total_page',
                     ],
                 ]);
    }

    public function test_get_produk()
    {
        $this->authenticateWithPaseto();

        $mockService = Mockery::mock(PengajuanService::class);
        
        $items = collect([
            [
                'id' => 123,
                'no_reg' => 'REG-20240101-001',
                'layanan' => 'KTP-el',
                'kecamatan' => 'Klojen',
                'pelapor' => 'Test User (Online)',
                'status' => 'DICETAK',
                'created_at' => '2024-01-01 08:00:00',
                'nama_identitas_produk' => 'Budi',
                'nomor' => '3573010101010001'
            ]
        ]);
        
        $paginator = new LengthAwarePaginator($items, 1, 10, 1);
        
        $mockService->shouldReceive('getProdukList')->once()->andReturn($paginator);
        
        $this->app->instance(PengajuanService::class, $mockService);

        $response = $this->getJson('/api/v1/pengajuan/produk');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'code',
                     'message',
                     'data' => [
                         '*' => [
                             'id',
                             'no_reg',
                             'layanan',
                             'kecamatan',
                             'pelapor',
                             'status',
                             'created_at',
                             'nama_identitas_produk',
                             'nomor'
                         ]
                     ],
                     'meta' => [
                         'page',
                         'per_page',
                         'total',
                         'total_page',
                     ],
                 ]);
    }

    public function test_get_detail_timeline()
    {
        $this->authenticateWithPaseto();

        $mockService = Mockery::mock(PengajuanService::class);
        
        $mockService->shouldReceive('getDetailTimeline')->once()->with(123)->andReturn([
            'ajuan_id' => 123,
            'no_reg' => 'REG-123',
            'status_saat_ini' => 'SELESAI',
            'timeline' => [
                [
                    'status' => 'MENUNGGU',
                    'note' => 'Menunggu verifikasi',
                    'datetime' => '2024-01-01 10:00:00'
                ]
            ]
        ]);
        
        $this->app->instance(PengajuanService::class, $mockService);

        $response = $this->getJson('/api/v1/pengajuan/123/detail');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'code',
                     'message',
                     'data' => [
                         'ajuan_id',
                         'no_reg',
                         'status_saat_ini',
                         'timeline' => [
                             '*' => [
                                 'status',
                                 'note',
                                 'datetime'
                             ]
                         ]
                     ]
                 ]);
    }
}

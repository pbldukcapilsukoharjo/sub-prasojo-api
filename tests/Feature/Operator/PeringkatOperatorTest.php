<?php

namespace Tests\Feature\Operator;

use App\Services\PeringkatOperatorService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Tests\TestCase;

class PeringkatOperatorTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_endpoint_returns_valid_structure()
    {
        $mockService = Mockery::mock(PeringkatOperatorService::class);
        $mockService->shouldReceive('index')->once()->andReturn([
            'total_layanan' => 100,
            'rata_rata_durasi' => 5.5,
            'tingkat_selesai' => 80.5,
            'peringkat_operator' => [
                'list' => [
                    [
                        'id' => 1,
                        'operator' => 'Test Operator',
                        'desa' => 'Desa A',
                        'kecamatan' => 'Kecamatan B',
                        'jumlah_ajuan' => 50,
                        'peringkat' => 1,
                    ]
                ],
                'meta' => [
                    'page' => 1,
                    'per_page' => 5,
                    'total' => 100,
                    'total_page' => 20
                ]
            ]
        ]);

        $this->app->instance(PeringkatOperatorService::class, $mockService);

        $response = $this->getJson('/api/v1/peringkat-operator');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'code',
                     'message',
                     'data' => [
                         'total_layanan',
                         'rata_rata_durasi',
                         'tingkat_selesai',
                         'peringkat_operator' => [
                             'list' => [
                                 '*' => [
                                     'id',
                                     'operator',
                                     'desa',
                                     'kecamatan',
                                     'jumlah_ajuan',
                                     'peringkat'
                                 ]
                             ],
                             'meta' => [
                                 'page',
                                 'per_page',
                                 'total',
                                 'total_page'
                             ]
                         ]
                     ]
                 ]);
    }

    public function test_show_endpoint_returns_valid_structure()
    {
        $mockService = Mockery::mock(PeringkatOperatorService::class);
        $mockService->shouldReceive('show')->once()->with(1)->andReturn([
            'id' => 1,
            'nama' => 'Test Operator',
            'total_ajuan' => 50,
            'total_selesai' => 45,
            'tingkat_selesai' => 90,
            'layanan_perbulan' => [
                'Jan' => 10, 'Feb' => 20, 'Mar' => 0, 'Apr' => 0,
                'Mei' => 0, 'Jun' => 0, 'Jul' => 0, 'Agu' => 0,
                'Sep' => 0, 'Okt' => 0, 'Nov' => 0, 'Des' => 0,
            ],
            'riwayat_layanan' => [
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
            ]
        ]);

        $this->app->instance(PeringkatOperatorService::class, $mockService);

        $response = $this->getJson('/api/v1/peringkat-operator/1');
        
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
                         'riwayat_layanan' => [
                             '*' => [
                                 'id',
                                 'no_regis',
                                 'pemohon',
                                 'kode_ajuan',
                                 'desa',
                                 'tanggal',
                                 'waktu',
                                 'status'
                             ]
                         ]
                     ]
                 ]);
    }

    public function test_show_endpoint_not_found()
    {
        $mockService = Mockery::mock(PeringkatOperatorService::class);
        $mockService->shouldReceive('show')->once()->with(99999)->andThrow(new ModelNotFoundException());

        $this->app->instance(PeringkatOperatorService::class, $mockService);

        $response = $this->getJson('/api/v1/peringkat-operator/99999');
        $response->assertStatus(404);
    }
}

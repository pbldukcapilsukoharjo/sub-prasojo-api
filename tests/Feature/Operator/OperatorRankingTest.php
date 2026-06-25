<?php

namespace Tests\Feature\Operator;

use App\Services\OperatorService;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class OperatorRankingTest extends TestCase
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
            'total_aktif' => 10,
            'total_berkas_dikerjakan' => 500,
            'rata_rata_kecepatan_text' => '15 Menit/Berkas'
        ]);

        $this->app->instance(OperatorService::class, $mockService);

        $response = $this->getJson('/api/v1/operator/kpi-global');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'code',
                     'message',
                     'data' => [
                         'total_aktif',
                         'total_berkas_dikerjakan',
                         'rata_rata_kecepatan_text'
                     ]
                 ]);
    }

    public function test_ranking_endpoint()
    {
        $mockService = Mockery::mock(OperatorService::class);
        
        $items = [
            (object) [
                'id_operator' => 1,
                'nama' => 'Test Operator',
                'total_berkas' => 50,
                'rata_rata_waktu_menit' => 10.5
            ]
        ];
        
        $paginator = new LengthAwarePaginator($items, 1, 10, 1);
        
        $mockService->shouldReceive('getRanking')->once()->andReturn($paginator);
        $this->app->instance(OperatorService::class, $mockService);

        $response = $this->getJson('/api/v1/operator/ranking');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'code',
                     'message',
                     'data' => [
                         '*' => [
                             'peringkat',
                             'id_operator',
                             'nama',
                             'total_berkas',
                             'rata_rata_waktu_menit'
                         ]
                     ],
                     'meta' => [
                         'page',
                         'per_page',
                         'total',
                         'total_page'
                     ]
                 ]);
    }

    public function test_detail_endpoint()
    {
        $mockService = Mockery::mock(OperatorService::class);
        $mockService->shouldReceive('getDetail')->once()->with(1)->andReturn([
            'profil' => [
                'nama' => 'Test Operator',
                'total_dikerjakan' => 50,
                'rata_rata_waktu_menit' => 10.5
            ],
            'riwayat_kerja' => [
                [
                    'no_reg' => 'REG-123',
                    'layanan' => 'KTP',
                    'waktu_mulai' => '08:00:00',
                    'waktu_selesai' => '08:10:00',
                    'durasi_menit' => 10
                ]
            ]
        ]);

        $this->app->instance(OperatorService::class, $mockService);

        $response = $this->getJson('/api/v1/operator/1/detail');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'code',
                     'message',
                     'data' => [
                         'profil' => [
                             'nama',
                             'total_dikerjakan',
                             'rata_rata_waktu_menit'
                         ],
                         'riwayat_kerja' => [
                             '*' => [
                                 'no_reg',
                                 'layanan',
                                 'waktu_mulai',
                                 'waktu_selesai',
                                 'durasi_menit'
                             ]
                         ]
                     ]
                 ]);
    }

    public function test_detail_not_found()
    {
        $mockService = Mockery::mock(OperatorService::class);
        $mockService->shouldReceive('getDetail')->once()->with(99999)->andThrow(new \Illuminate\Database\Eloquent\ModelNotFoundException());

        $this->app->instance(OperatorService::class, $mockService);

        $response = $this->getJson('/api/v1/operator/99999/detail');
        $response->assertStatus(404);
    }
}

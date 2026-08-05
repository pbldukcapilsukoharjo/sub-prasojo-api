<?php

namespace Tests\Feature\Wilayah;

use App\Services\WilayahService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class WilayahDistribusiTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_distribusi_endpoint()
    {
        $this->authenticateWithPaseto();

        $mockService = Mockery::mock(WilayahService::class);
        
        $items = [
            [
                'id_kecamatan' => '35.73.01',
                'nama_kecamatan' => 'Klojen',
                'total_ajuan' => 4500,
                'rata_rata_waktu' => '2 Jam',
                'rasio_selesai_persen' => 95.5
            ]
        ];
        
        $paginator = new LengthAwarePaginator($items, 1, 10, 1);
        
        $mockService->shouldReceive('getDistribusi')->once()->andReturn($paginator);
        $this->app->instance(WilayahService::class, $mockService);

        $response = $this->getJson('/api/v1/wilayah/distribusi');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'code',
                     'message',
                     'data' => [
                         '*' => [
                             'id_kecamatan',
                             'nama_kecamatan',
                             'total_ajuan',
                             'rata_rata_waktu',
                             'rasio_selesai_persen'
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

    public function test_distribusi_endpoint_with_search_parameter()
    {
        $this->authenticateWithPaseto();

        $mockService = Mockery::mock(WilayahService::class);
        $items = [
            [
                'id_kecamatan' => '35.73.01',
                'nama_kecamatan' => 'Klojen',
                'total_ajuan' => 10,
                'rata_rata_waktu' => '1 Jam',
                'rasio_selesai_persen' => 100.0
            ]
        ];
        $paginator = new LengthAwarePaginator($items, 1, 10, 1);

        $mockService->shouldReceive('getDistribusi')
            ->once()
            ->withArgs(function ($filter, $perPage) {
                return isset($filter->request['search']) && $filter->request['search'] === 'Klojen';
            })
            ->andReturn($paginator);

        $this->app->instance(WilayahService::class, $mockService);

        $response = $this->getJson('/api/v1/wilayah/distribusi?search=Klojen');

        $response->assertStatus(200)
                 ->assertJsonPath('data.0.nama_kecamatan', 'Klojen');
    }

    public function test_distribusi_endpoint_with_q_parameter()
    {
        $this->authenticateWithPaseto();

        $mockService = Mockery::mock(WilayahService::class);
        $items = [
            [
                'id_kecamatan' => '35.73.01',
                'nama_kecamatan' => 'Klojen',
                'total_ajuan' => 10,
                'rata_rata_waktu' => '1 Jam',
                'rasio_selesai_persen' => 100.0
            ]
        ];
        $paginator = new LengthAwarePaginator($items, 1, 10, 1);

        $mockService->shouldReceive('getDistribusi')
            ->once()
            ->withArgs(function ($filter, $perPage) {
                return isset($filter->request['search']) && $filter->request['search'] === 'Klojen';
            })
            ->andReturn($paginator);

        $this->app->instance(WilayahService::class, $mockService);

        $response = $this->getJson('/api/v1/wilayah/distribusi?q=Klojen');

        $response->assertStatus(200)
                 ->assertJsonPath('data.0.nama_kecamatan', 'Klojen');
    }
}


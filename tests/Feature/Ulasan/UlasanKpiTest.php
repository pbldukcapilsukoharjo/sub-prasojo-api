<?php

namespace Tests\Feature\Ulasan;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Monitoring\SubUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Services\UlasanService;
use Mockery;
use Mockery\MockInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class UlasanKpiTest extends TestCase
{
    use RefreshDatabase;

    public function test_kpi_endpoint_returns_successful_response()
    {
        $this->instance(
            UlasanService::class,
            Mockery::mock(UlasanService::class, function (MockInterface $mock) {
                $mock->shouldReceive('getKpi')->once()->andReturn([
                    'rata_rata_bintang' => 4.5,
                    'distribusi' => [
                        'bintang_5' => 10,
                        'bintang_4' => 5,
                        'bintang_3' => 2,
                        'bintang_2' => 1,
                        'bintang_1' => 0
                    ]
                ]);
            })
        );

        $this->authenticateWithPaseto();
        
        $response = $this->getJson('/api/v1/ulasan/kpi');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'code',
                     'message',
                     'data' => [
                         'rata_rata_bintang',
                         'distribusi' => [
                             'bintang_5',
                             'bintang_4',
                             'bintang_3',
                             'bintang_2',
                             'bintang_1'
                         ]
                     ]
                 ]);
    }

    public function test_list_endpoint_returns_successful_response()
    {
        $this->instance(
            UlasanService::class,
            Mockery::mock(UlasanService::class, function (MockInterface $mock) {
                $data = collect([
                    [
                        'id_review' => 1,
                        'tanggal' => '2024-01-01',
                        'no_reg' => 'REG-123',
                        'layanan' => 'KTP-el',
                        'rating' => 5,
                        'komentar' => 'Pelayanan sangat cepat dan memuaskan!'
                    ]
                ]);
                $paginator = new LengthAwarePaginator($data, 1, 10, 1);
                $mock->shouldReceive('getList')->once()->andReturn($paginator);
            })
        );

        $this->authenticateWithPaseto();
        
        $response = $this->getJson('/api/v1/ulasan');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'code',
                     'message',
                     'data',
                     'meta' => [
                         'page',
                         'per_page',
                         'total',
                         'total_page'
                     ]
                 ]);
    }

    public function test_export_endpoint_returns_excel_file()
    {
        $this->instance(
            UlasanService::class,
            Mockery::mock(UlasanService::class, function (MockInterface $mock) {
                $data = collect([
                    [
                        'id_review' => 1,
                        'tanggal' => '2024-01-01',
                        'no_reg' => 'REG-123',
                        'layanan' => 'KTP-el',
                        'rating' => 5,
                        'komentar' => 'Pelayanan sangat cepat dan memuaskan!'
                    ]
                ]);
                $mock->shouldReceive('getForExport')->once()->andReturn($data);
            })
        );

        $this->authenticateWithPaseto();
        
        $response = $this->get('/api/v1/ulasan/export');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_ulasan_endpoints_accept_pelapor_parameter()
    {
        $this->instance(
            UlasanService::class,
            Mockery::mock(UlasanService::class, function (MockInterface $mock) {
                $mock->shouldReceive('getKpi')
                    ->once()
                    ->withArgs(fn($filter) => $filter->request['pelapor'] === 'online')
                    ->andReturn([
                        'rata_rata_bintang' => 4.5,
                        'distribusi' => [
                            'bintang_5' => 10,
                            'bintang_4' => 5,
                            'bintang_3' => 2,
                            'bintang_2' => 1,
                            'bintang_1' => 0
                        ]
                    ]);
            })
        );

        $this->authenticateWithPaseto();
        
        $response = $this->getJson('/api/v1/ulasan/kpi?pelapor=online');

        $response->assertStatus(200);
    }
}

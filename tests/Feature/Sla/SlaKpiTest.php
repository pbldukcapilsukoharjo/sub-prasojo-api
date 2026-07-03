<?php

namespace Tests\Feature\Sla;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Monitoring\SubUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Services\SLAService;
use Mockery;
use Mockery\MockInterface;

class SlaKpiTest extends TestCase
{
    use RefreshDatabase;

    public function test_kpi_endpoint_returns_successful_response()
    {
        $this->instance(
            SLAService::class,
            Mockery::mock(SLAService::class, function (MockInterface $mock) {
                $mock->shouldReceive('getKpi')->once()->andReturn([
                    'rata_rata_global_text' => '2 Jam 30 Menit',
                    'capaian_sla_persen' => 85.5
                ]);
            })
        );

        $this->authenticateWithPaseto();
        
        $response = $this->getJson('/api/v1/sla/kpi');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'code',
                     'message',
                     'data' => [
                         'rata_rata_global_text',
                         'capaian_sla_persen'
                     ]
                 ]);
    }

    public function test_layanan_endpoint_returns_successful_response()
    {
        $this->instance(
            SLAService::class,
            Mockery::mock(SLAService::class, function (MockInterface $mock) {
                $mock->shouldReceive('index')->once()->andReturn([
                    'rata_rata_waktu_proses' => 2.5,
                    'pencapaian_sla' => 85,
                    'target_sla' => 6,
                    'jumlah_ajuan' => 100,
                    'daftar_rincian' => [
                        'list' => [
                            [
                                'id' => 1,
                                'jenis_layanan' => 'KTP',
                                'jumlah_ajuan' => 10,
                                'rata_rata_waktu' => 2.0
                            ]
                        ],
                        'meta' => [
                            'page' => 1,
                            'per_page' => 10,
                            'total' => 1,
                            'total_page' => 1
                        ]
                    ]
                ]);
            })
        );

        $this->authenticateWithPaseto();
        
        $response = $this->getJson('/api/v1/sla');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'code',
                     'message',
                     'data' => [
                         'rata_rata_waktu_proses',
                         'pencapaian_sla',
                         'target_sla',
                         'jumlah_ajuan',
                         'daftar_rincian' => [
                             'list' => [
                                 '*' => [
                                     'id',
                                     'jenis_layanan',
                                     'jumlah_ajuan',
                                     'rata_rata_waktu'
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

    public function test_export_endpoint_returns_excel_file()
    {
        $this->instance(
            SLAService::class,
            Mockery::mock(SLAService::class, function (MockInterface $mock) {
                $data = collect([
                    [
                        'layanan_kode' => 'KTP',
                        'nama_layanan' => 'KTP',
                        'target_sla' => '6 Jam',
                        'aktual_rata_rata' => '2 Jam',
                        'status_sla' => 'MEMENUHI'
                    ]
                ]);
                $mock->shouldReceive('export')->once()->andReturn($data);
            })
        );

        $this->authenticateWithPaseto();
        
        $response = $this->get('/api/v1/sla/export');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}

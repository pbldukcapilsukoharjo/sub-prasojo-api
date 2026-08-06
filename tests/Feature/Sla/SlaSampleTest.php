<?php

namespace Tests\Feature\Sla;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\SLAService;
use Mockery;
use Mockery\MockInterface;

class SlaSampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_samples_endpoint_returns_successful_response()
    {
        $this->instance(
            SLAService::class,
            Mockery::mock(SLAService::class, function (MockInterface $mock) {
                $mock->shouldReceive('getSamples')->once()->andReturn([
                    'list' => [
                        [
                            'ajuan_id' => 101,
                            'ajuan_no_reg' => 'REG-2026-0001',
                            'ajuan_layanan_kode' => 'KTP',
                            'ajuan_pelapor_role_name' => 'Desa',
                            'ajuan_is_online' => true,
                            'ajuan_create_datetime' => '2026-08-01 08:00:00',
                            'waktu_mulai' => '2026-08-01 08:10:00',
                            'waktu_selesai' => '2026-08-01 09:30:00',
                            'durasi_sla_menit' => 80,
                            'target_sla_menit_aktual' => 360,
                            'layanan' => (object) ['layanan_nama' => 'KARTU TANDA PENDUDUK'],
                            'pelapor' => (object) ['fullname' => 'Pelapor Desa'],
                        ]
                    ],
                    'meta' => [
                        'page' => 1,
                        'per_page' => 10,
                        'total' => 1,
                        'total_page' => 1
                    ]
                ]);
            })
        );

        $this->authenticateWithPaseto();
        
        $response = $this->getJson('/api/v1/sla/samples?kategori=tercepat');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'code',
                     'message',
                     'data' => [
                         '*' => [
                             'ajuan_id',
                             'no_reg',
                             'layanan_kode',
                             'jenis_layanan',
                             'pelapor_role',
                             'pelapor_nama',
                             'pelapor_channel',
                             'pelapor_display',
                             'tanggal_diterima',
                             'waktu_mulai_proses',
                             'waktu_selesai',
                             'durasi_penyelesaian_menit',
                             'durasi_penyelesaian_text',
                             'target_sla_menit',
                             'target_sla_text',
                             'status_sla',
                             'is_tepat_waktu',
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

    public function test_samples_endpoint_supports_manual_selection_and_pelapor_filter()
    {
        $this->instance(
            SLAService::class,
            Mockery::mock(SLAService::class, function (MockInterface $mock) {
                $mock->shouldReceive('getSamples')
                    ->once()
                    ->with(Mockery::on(function ($args) {
                        return isset($args['ajuan_id']) && $args['ajuan_id'] == 105 && isset($args['pelapor']) && $args['pelapor'] === 'Online';
                    }))
                    ->andReturn([
                        'list' => [
                            [
                                'ajuan_id' => 105,
                                'ajuan_no_reg' => 'REG-2026-0105',
                                'ajuan_layanan_kode' => 'AKL',
                                'ajuan_pelapor_role_name' => 'Warga',
                                'ajuan_is_online' => true,
                                'ajuan_create_datetime' => '2026-08-02 10:00:00',
                                'waktu_mulai' => '2026-08-02 10:15:00',
                                'waktu_selesai' => '2026-08-02 11:45:00',
                                'durasi_sla_menit' => 90,
                                'target_sla_menit_aktual' => 360,
                                'layanan' => (object) ['layanan_nama' => 'AKTA KELAHIRAN'],
                                'pelapor' => (object) ['fullname' => 'Warga Mandiri'],
                            ]
                        ],
                        'meta' => [
                            'page' => 1,
                            'per_page' => 10,
                            'total' => 1,
                            'total_page' => 1
                        ]
                    ]);
            })
        );

        $this->authenticateWithPaseto();
        
        $response = $this->getJson('/api/v1/sla/samples?ajuan_id=105&pelapor=Online');

        $response->assertStatus(200)
                 ->assertJsonPath('data.0.ajuan_id', 105)
                 ->assertJsonPath('data.0.status_sla', 'Tepat Waktu');
    }
}

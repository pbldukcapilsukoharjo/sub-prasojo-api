<?php

namespace Tests\Feature\Sla;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\SubUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Services\SlaService;
use Mockery;
use Mockery\MockInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class SlaKpiTest extends TestCase
{
    use RefreshDatabase;

    protected function getAuthToken($user)
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123'
        ]);

        return $response->json('data.token');
    }

    protected function createAuthUser()
    {
        $user = SubUser::create([
            'id' => Str::uuid(),
            'fullname' => 'Test User SLA',
            'email' => 'testsla@example.com',
            'hashed_password' => Hash::make('password123'),
        ]);

        return $user;
    }

    public function test_kpi_endpoint_returns_successful_response()
    {
        $this->instance(
            SlaService::class,
            Mockery::mock(SlaService::class, function (MockInterface $mock) {
                $mock->shouldReceive('getKpi')->once()->andReturn([
                    'rata_rata_global_text' => '2 Jam 30 Menit',
                    'capaian_sla_persen' => 85.5
                ]);
            })
        );

        $user = $this->createAuthUser();
        $token = $this->getAuthToken($user);
        
        $response = $this->getJson('/api/v1/sla/kpi', [
            'Authorization' => "Bearer $token"
        ]);

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
            SlaService::class,
            Mockery::mock(SlaService::class, function (MockInterface $mock) {
                $data = collect([
                    [
                        'layanan_kode' => 'KTP',
                        'nama_layanan' => 'KTP',
                        'target_sla' => '6 Jam',
                        'aktual_rata_rata' => '2 Jam',
                        'status_sla' => 'MEMENUHI'
                    ]
                ]);
                $paginator = new LengthAwarePaginator($data, 1, 10, 1);
                $mock->shouldReceive('getLayanan')->once()->andReturn($paginator);
            })
        );

        $user = $this->createAuthUser();
        $token = $this->getAuthToken($user);
        
        $response = $this->getJson('/api/v1/sla/layanan', [
            'Authorization' => "Bearer $token"
        ]);

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
            SlaService::class,
            Mockery::mock(SlaService::class, function (MockInterface $mock) {
                $data = collect([
                    [
                        'layanan_kode' => 'KTP',
                        'nama_layanan' => 'KTP',
                        'target_sla' => '6 Jam',
                        'aktual_rata_rata' => '2 Jam',
                        'status_sla' => 'MEMENUHI'
                    ]
                ]);
                $mock->shouldReceive('exportLayanan')->once()->andReturn($data);
            })
        );

        $user = $this->createAuthUser();
        $token = $this->getAuthToken($user);
        
        $response = $this->get('/api/v1/sla/export', [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}

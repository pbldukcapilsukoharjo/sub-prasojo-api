<?php

namespace Tests\Feature\Pengajuan;

use App\Services\PengajuanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class PengajuanListTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_get_pengajuan_list()
    {
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
        
        $mockService->shouldReceive('getList')->once()->andReturn($paginator);
        
        $this->app->instance(PengajuanService::class, $mockService);

        // Not using auth middleware for mock tests if it's mocked, but let's assume it might be needed.
        // The endpoint is actually currently not protected by paseto in the test context if we just hit it.
        // Wait, the routes in api.php for pengajuan are NOT inside paseto.auth middleware group currently!
        // Let's verify this and fix if needed. For now just test the structure.
        $response = $this->getJson('/api/v1/pengajuan');

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

    public function test_can_export_pengajuan()
    {
        $mockService = Mockery::mock(PengajuanService::class);
        
        // Return a dummy response for export
        $mockService->shouldReceive('export')->once()->andReturn(response('dummy excel content', 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]));
        
        $this->app->instance(PengajuanService::class, $mockService);

        $response = $this->get('/api/v1/pengajuan/export');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}

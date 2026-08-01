<?php

namespace Tests\Unit\Services;

use App\Filters\SlaFilter;
use App\Services\SLAService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SlaServiceTest extends TestCase
{
    public function test_get_kpi_uses_cache()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn([
                'rata_rata_global_text' => '2 Jam 30 Menit',
                'capaian_sla_persen' => 85.5,
            ]);

        $service = new SLAService();

        $result = $service->getKpi([]);

        $this->assertEquals('2 Jam 30 Menit', $result['rata_rata_global_text']);
        $this->assertEquals(85.5, $result['capaian_sla_persen']);
    }
}

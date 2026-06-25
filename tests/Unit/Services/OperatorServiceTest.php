<?php

namespace Tests\Unit\Services;

use App\Filters\OperatorFilter;
use App\Services\OperatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OperatorServiceTest extends TestCase
{
    public function test_get_kpi_global_uses_cache()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn([
                'total_aktif' => 5,
                'total_berkas_dikerjakan' => 100,
                'rata_rata_kecepatan_text' => '15 Menit/Berkas',
            ]);

        $filter = new OperatorFilter([]);
        $service = new OperatorService();

        $result = $service->getKpiGlobal($filter);

        $this->assertEquals(5, $result['total_aktif']);
        $this->assertEquals('15 Menit/Berkas', $result['rata_rata_kecepatan_text']);
    }
}

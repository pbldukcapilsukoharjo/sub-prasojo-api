<?php

namespace Tests\Unit\Services;

use App\Filters\UlasanFilter;
use App\Services\UlasanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UlasanServiceTest extends TestCase
{
    public function test_get_kpi_uses_cache()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn([
                'rata_rata_bintang' => 4.5,
                'distribusi' => [
                    'bintang_5' => 10,
                ]
            ]);

        $filter = new UlasanFilter([]);
        $service = new UlasanService();

        $result = $service->getKpi($filter);

        $this->assertEquals(4.5, $result['rata_rata_bintang']);
        $this->assertEquals(10, $result['distribusi']['bintang_5']);
    }
}

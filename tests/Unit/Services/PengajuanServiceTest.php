<?php

namespace Tests\Unit\Services;

use App\Services\PengajuanService;
use Tests\TestCase;

class PengajuanServiceTest extends TestCase
{
    public function test_service_can_be_instantiated()
    {
        $service = new PengajuanService();
        $this->assertInstanceOf(PengajuanService::class, $service);
    }
}

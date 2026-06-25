<?php

namespace Tests\Unit\Services;

use App\Services\WilayahService;
use Tests\TestCase;

class WilayahServiceTest extends TestCase
{
    public function test_service_can_be_instantiated()
    {
        $service = new WilayahService();
        $this->assertInstanceOf(WilayahService::class, $service);
    }
}

<?php

namespace Tests\Unit\Modules\Rmsapi;

use App\Modules\Rmsapi\src\RmsapiModuleServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    /** @test */
    public function test_module_basic_functionality(): void
    {
        RmsapiModuleServiceProvider::enableModule();

        $this->assertTrue(true, 'Most basic test... to be continued');
    }
}

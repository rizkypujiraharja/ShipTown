<?php

namespace Tests\Unit\Modules\PrintNode;

use App\Modules\PrintNode\src\PrintNodeServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    /** @test */
    public function test_module_basic_functionality(): void
    {
        PrintNodeServiceProvider::enableModule();

        $this->assertTrue(true, 'Most basic test... to be continued');
    }
}

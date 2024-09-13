<?php

namespace Tests\Unit\Modules\BoxTop;

use App\Modules\BoxTop\src\BoxTopServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    /** @test */
    public function test_module_basic_functionality(): void
    {
        BoxTopServiceProvider::enableModule();

        $this->assertTrue(true, 'Make sure no exceptions when enabling');
    }
}

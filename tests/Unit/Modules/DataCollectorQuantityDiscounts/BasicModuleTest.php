<?php

namespace Tests\Unit\Modules\DataCollectorQuantityDiscounts;

use App\Modules\DataCollectorQuantityDiscounts\src\QuantityDiscountsServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        QuantityDiscountsServiceProvider::enableModule();
    }

    /** @test */
    public function testBasicFunctionality(): void
    {
        $this->assertTrue(true, 'Each quantity discounts has its own tests');
    }
}

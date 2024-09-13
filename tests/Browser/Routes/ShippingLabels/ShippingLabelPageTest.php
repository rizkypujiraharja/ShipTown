<?php

namespace Tests\Browser\Routes\ShippingLabels;

use Tests\DuskTestCase;
use Throwable;

class ShippingLabelPageTest extends DuskTestCase
{
    private string $uri = '/shipping-labels/{shipping_label}';

    /**
     * @throws Throwable
     */
    public function testPage(): void
    {
        $this->markTestIncomplete();
    }
}

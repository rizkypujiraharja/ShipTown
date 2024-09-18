<?php

namespace App\Modules\Rmsapi\src\Jobs;

use Tests\TestCase;

class RepublishWebhooksForDiscrepenciesTest extends TestCase
{
    public function test_basic_functionality()
    {
        RepublishWebhooksForDiscrepencies::dispatchSync();

        $this->assertTrue(true, 'We ran the job without errors');
    }
}

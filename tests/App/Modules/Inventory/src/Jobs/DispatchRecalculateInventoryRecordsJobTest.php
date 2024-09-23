<?php

namespace Tests\App\Modules\Inventory\src\Jobs;

use App\Modules\Inventory\src\Jobs\DispatchRecalculateInventoryRecordsJob;
use Tests\TestCase;

class DispatchRecalculateInventoryRecordsJobTest extends TestCase
{
    public function test_basic_functionality()
    {
        DispatchRecalculateInventoryRecordsJob::dispatch();

        $this->assertTrue(true, 'We ran the job without errors');
    }
}

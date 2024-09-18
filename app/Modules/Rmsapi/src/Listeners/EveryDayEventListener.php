<?php

namespace App\Modules\Rmsapi\src\Listeners;

use App\Modules\Rmsapi\src\Jobs\CleanupImportTablesJob;
use App\Modules\Rmsapi\src\Jobs\RepublishWebhooksForDiscrepencies;

class EveryDayEventListener
{
    public function handle()
    {
        CleanupImportTablesJob::dispatch();
        RepublishWebhooksForDiscrepencies::dispatch();
    }
}

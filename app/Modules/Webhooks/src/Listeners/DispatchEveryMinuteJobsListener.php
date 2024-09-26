<?php

namespace App\Modules\Webhooks\src\Listeners;

use App\Modules\Webhooks\src\Services\WebhooksService;

class DispatchEveryMinuteJobsListener
{
    public function handle(): void
    {
        WebhooksService::dispatchJobs();
    }
}

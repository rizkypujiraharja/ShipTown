<?php

namespace App\Modules\QueueMonitor\src\Listeners;

use App\Modules\QueueMonitor\src\QueueMonitorServiceProvider;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class JobProcessedListener
{
    public function handle(JobProcessed $event)
    {
        $jobName = data_get($event->job->payload(), 'displayName');

        if (in_array($jobName, QueueMonitorServiceProvider::$ignoredJobList)) {
            return;
        }

        $key = implode('-', ['job_start_time', $event->job->uuid()]);

        $jobStartTime = Cache::pull($key);

        Log::info('Job processed', ['job' => $jobName, 'duration' => (int)now()->diffInSeconds($jobStartTime)]);
    }
}

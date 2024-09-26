<?php

namespace App\Modules\QueueMonitor\src\Listeners;

use App\Modules\QueueMonitor\src\QueueMonitorServiceProvider;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class JobFailedListener
{
    public function handle(JobFailed $event)
    {
        $jobName = data_get($event->job->payload(), 'displayName');

        if (in_array($jobName, QueueMonitorServiceProvider::$ignoredJobList)) {
            return;
        }

        $key = implode('-', ['job_start_time', $event->job->uuid()]);

        $jobStartTime = Cache::pull($key);

        Log::info('Job failed', [
            'job' => $jobName,
            'duration' => (int) now()->diffInSeconds($jobStartTime),
            'exception' => $event->exception,
        ]);
    }
}

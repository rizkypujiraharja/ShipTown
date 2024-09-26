<?php

use App\Jobs\DispatchEveryDayEventJob;
use App\Jobs\DispatchEveryFiveMinutesEventJob;
use App\Jobs\DispatchEveryHourEventJobs;
use App\Jobs\DispatchEveryMinuteEventJob;
use App\Jobs\DispatchEveryMonthEventJob;
use App\Jobs\DispatchEveryTenMinutesEventJob;
use App\Jobs\DispatchEveryWeekEventJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::job(new DispatchEveryMinuteEventJob)->everyMinute();
Schedule::job(new DispatchEveryFiveMinutesEventJob)->everyFiveMinutes();
Schedule::job(new DispatchEveryTenMinutesEventJob)->everyTenMinutes();
Schedule::job(new DispatchEveryHourEventJobs)->hourly();
Schedule::job(new DispatchEveryDayEventJob)->dailyAt('22:00');
Schedule::job(new DispatchEveryWeekEventJob)->weekly();
Schedule::job(new DispatchEveryMonthEventJob)->monthly();

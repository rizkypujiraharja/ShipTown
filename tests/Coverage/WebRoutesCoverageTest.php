<?php

namespace Tests\Coverage;

use App\Console\Commands\AppGenerateRoutesTests;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebRoutesCoverageTest extends TestCase
{
    /**
     * A basic test to make sure all routes have minimum one test file.
     */
    public function test_if_all_web_routes_have_test_file(): void
    {
        Artisan::call('route:list --json --env=production');

        collect(json_decode(Artisan::output()))
            ->filter(function ($route) {
                $isNotApiRoute = ! Str::startsWith($route->uri, 'api');
                $isNotDevRoute = ! Str::startsWith($route->uri, '_');
                $isGetMethod = $route->method === 'GET|HEAD';

                return $isNotApiRoute && $isNotDevRoute && $isGetMethod;
            })
            ->map(function ($route) {
                $fullFileName = app()->basePath();

                $fullFileName .= '/tests/';
                $fullFileName .= AppGenerateRoutesTests::getWebRouteTestName($route);
                $fullFileName .= '.php';

                return $fullFileName;
            })
            ->each(function ($fileName) {
                $this->assertFileExists($fileName, 'run "php artisan app:generate-routes-tests"');
            });
    }
}

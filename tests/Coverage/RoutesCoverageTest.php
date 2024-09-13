<?php

namespace Tests\Coverage;

use App\Console\Commands\AppGenerateRoutesTests;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RoutesCoverageTest extends TestCase
{
    /**
     * A basic test to make sure all routes have minimum one test file.
     */
    public function test_if_all_api_routes_have_test_file(): void
    {
        Artisan::call('route:list --json --path=api --env=production');

        $artisanOutput = json_decode(Artisan::output());

        collect($artisanOutput)
            ->each(function ($route) {
                $fullFileName = app()->basePath();
                $fullFileName .= '/tests/';
                $fullFileName .= AppGenerateRoutesTests::getWebRouteTestName($route);
                $fullFileName .= '.php';

                if (! file_exists($fullFileName)) {
                    $this->assertFileExists($fullFileName, 'Run "php artisan app:generate-routes-tests"');
                }
                $this->assertFileExists($fullFileName, 'Run "php artisan app:generate-routes-tests"');
            });

        $this->assertNotEmpty($artisanOutput, 'Artisan route:list command did not return any routes');
    }
}

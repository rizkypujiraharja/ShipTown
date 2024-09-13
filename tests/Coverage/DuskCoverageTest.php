<?php

namespace Tests\Coverage;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Tests\TestCase;

class DuskCoverageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Enable 2FA auth
        $this->app['config']->set('two_factor_auth.enabled', true);
        $this->app['config']->set('two_factor_auth.disabled', false);
    }

    /**
     * A basic test to make sure all routes have minimum one test file.
     */
    public function test_if_all_web_routes_have_test_file(): void
    {
        Artisan::call('route:list --json --env=production');

        $routesCollection = collect(json_decode(Artisan::output()))
            ->filter(function ($route) {
                $isNotApiRoute = ! Str::startsWith($route->uri, 'api');
                $isGetMethod = $route->method === 'GET|HEAD';
                $isNotDevRoute = ! Str::startsWith($route->uri, '_');

                return $isNotApiRoute && $isNotDevRoute && $isGetMethod;
            })
            ->map(function ($route) {
                $fullFileName = app()->basePath();
                $fullFileName .= '/tests/Browser/';
                $fullFileName .= $this->getWebRouteTestName($route);
                $fullFileName .= '.php';

                return $fullFileName;
            });

        ray($routesCollection->toArray());

        $routesCollection->each(function ($fileName) {
            $this->assertFileExists($fileName, 'run "php artisan app:generate-dusk-tests"');
        });
    }

    private function getWebRouteTestName($route): string
    {
        $uri = Str::title($route->uri);
        $routeName = 'Routes/'.$uri.'PageTest';

        $routeName = str_replace('-', '', $routeName);
        $routeName = str_replace('_', '', $routeName);
        $routeName = str_replace('.', '', $routeName);
        $routeName = str_replace('{', '', $routeName);
        $routeName = str_replace('}', '', $routeName);

        return implode('/', collect(explode('/', $routeName))
            ->map(function ($part) {
                return Str::ucfirst($part);
            })
            ->toArray());
    }
}

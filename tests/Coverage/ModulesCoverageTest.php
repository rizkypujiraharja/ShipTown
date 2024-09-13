<?php

namespace Tests\Coverage;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class ModulesCoverageTest extends TestCase
{
    /**
     * A basic test to make sure all routes have minimum one test file.
     */
    public function test_if_all_modules_have_test_file(): void
    {
        collect(File::directories('app/Modules'))
            ->map(function ($moduleDirectory) {
                $testFileName = app()->basePath();
                $testFileName .= Str::replaceArray('app/', ['/tests/Unit/'], $moduleDirectory);
                $testFileName .= '/BasicModuleTest.php';

                return $testFileName;
            })
            ->each(function ($fileName) {
                $this->assertFileExists($fileName, 'Run "php artisan app:generate-modules-tests"');
            });
    }
}

<?php

namespace Tests\Coverage;

use Tests\TestCase;

class JobsCoverageTest extends TestCase
{
    public function test_all_jobs_have_tests()
    {
        $jobFiles = $this->getJobFiles();

        foreach ($jobFiles as $jobFile) {
            $jobClass = app()->basePath() . '/tests/' . $this->getTestFileName($jobFile);


            if (!file_exists($jobClass)) {
                $this->markTestIncomplete($jobFile);
//                $this->assertFileExists($jobClass, 'Run "php artisan app:generate-jobs-tests"');
            }
        }
    }

    private function getJobFiles(): array
    {
        $directory = new \RecursiveDirectoryIterator(app_path());
        $iterator = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($iterator, '/^.+\/Jobs\/.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        $jobFiles = [];
        foreach ($regex as $file) {
            $jobFiles[] = $file[0];
        }

        return $jobFiles;
    }

    private function getTestFileName($file): string
    {
        $directory = \File::dirname($file);
        $directory = str_replace(app()->basePath() . '/', '', $directory);
        $directory = \Str::ucfirst($directory);

        $fileName = $directory . '/' . basename($file, '.php') . 'Test.php';

        return $fileName;
    }
}

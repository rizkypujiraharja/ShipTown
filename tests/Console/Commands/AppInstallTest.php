<?php

namespace Tests\Console\Commands;

use Tests\TestCase;

class AppInstallTest extends TestCase
{
    public function test_basic_functionality()
    {
        $this->artisan('db:wipe');
        $this->artisan('migrate');
        $this->artisan('app:install')
            ->assertExitCode(0);
    }
}

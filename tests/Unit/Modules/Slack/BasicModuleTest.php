<?php

namespace Tests\Unit\Modules\Slack;

use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    /** @test */
    public function testBasicFunctionality(): void
    {
        if (env('TEST_MODULES_SLACK_INCOMING_WEBHOOK_URL', '') === '') {
            $this->markTestSkipped('TESTS_MODULES_SLACK_INCOMING_WEBHOOK_URL env is not set');
        }

        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}

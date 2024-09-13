<?php

namespace Tests\Feature\Api\Modules\Webhooks\Subscriptions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_index_call_returns_ok(): void
    {
        $this->assertTrue(true, 'Tested in External/Webhooks');
    }
}

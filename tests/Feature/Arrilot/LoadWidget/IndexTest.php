<?php

namespace Tests\Feature\Arrilot\LoadWidget;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected string $uri = '/arrilot/load-widget';

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_if_uri_set(): void
    {
        $this->assertNotEmpty($this->uri);
    }
}

<?php

namespace Tests\Feature\Login;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    protected string $uri = '/login';

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_guest_call(): void
    {
        $response = $this->get($this->uri);

        $response->assertSuccessful();
    }

    /** @test */
    public function test_user_call(): void
    {
        $this->actingAs($this->user, 'web');

        $response = $this->get($this->uri);

        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function test_admin_call(): void
    {
        $this->actingAs($this->user, 'web');

        $this->user->assignRole('admin');

        $response = $this->get($this->uri);

        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function test_if_uri_set(): void
    {
        $this->assertNotEmpty($this->uri);
    }
}

<?php

namespace Tests\Feature\Csv\Products\Shipped;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected string $uri = 'csv/products/shipped';

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

    /** @test */
    public function test_guest_call(): void
    {
        $response = $this->get($this->uri);

        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_user_call(): void
    {
        $this->actingAs($this->user, 'web');

        $response = $this->get($this->uri);

        $response->assertSuccessful();
    }

    /** @test */
    public function test_admin_call(): void
    {
        $this->user->assignRole('admin');

        $this->actingAs($this->user, 'web');

        $response = $this->get($this->uri);

        $response->assertSuccessful();
    }
}

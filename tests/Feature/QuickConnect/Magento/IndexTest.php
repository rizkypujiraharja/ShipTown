<?php

namespace Tests\Feature\QuickConnect\Magento;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected string $uri = '/quick-connect/magento';

    protected mixed $user;

    protected function setUp(): void
    {
        parent::setUp();
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
        /** @var User user */
        $user = User::factory()->create();

        $this->actingAs($user, 'web');

        $response = $this->get($this->uri);

        $response->assertSuccessful();
    }

    /** @test */
    public function test_admin_call(): void
    {
        /** @var User user */
        $user = User::factory()->create();

        $user->assignRole('admin');

        $this->actingAs($user, 'web');

        $response = $this->get($this->uri);

        $response->assertSuccessful();
    }
}

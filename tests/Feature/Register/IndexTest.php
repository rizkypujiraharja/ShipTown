<?php

namespace Tests\Feature\Register;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected string $uri = '/register';

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

        if (User::query()->doesntExist()) {
            $response->assertOk();
        } else {
            $response->assertRedirect();
        }
    }

    /** @test */
    public function test_user_call(): void
    {
        $this->actingAs($this->user, 'web')
            ->get($this->uri)
            ->assertRedirect();
    }

    /** @test */
    public function test_admin_call(): void
    {
        $this->user->assignRole('admin');

        $this->actingAs($this->user, 'web')
            ->get($this->uri)
            ->assertRedirect();
    }
}

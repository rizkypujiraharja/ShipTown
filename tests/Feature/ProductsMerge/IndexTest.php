<?php

namespace Tests\Feature\ProductsMerge;

use App\Models\Product;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected string $uri = '/products-merge?sku1=sku1&sku2=sku2';

    protected mixed $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        Product::factory()->create(['sku' => 'sku1']);
        Product::factory()->create(['sku' => 'sku2']);
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

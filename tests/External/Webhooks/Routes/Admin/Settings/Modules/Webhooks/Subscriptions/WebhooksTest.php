<?php

namespace Tests\External\Webhooks\Routes\Admin\Settings\Modules\Webhooks\Subscriptions;

use App\Modules\Webhooks\src\WebhooksServiceProviderBase;
use App\User;
use Tests\TestCase;

class WebhooksTest extends TestCase
{
    protected string $uri = '/settings/modules/webhooks/subscriptions';

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        WebhooksServiceProviderBase::enableModule();
    }

    /** @test */
    public function test_if_uri_set(): void
    {
        $this->assertNotEmpty($this->uri);
    }
    //
    //    /** @test */
    //    public function test_guest_call()
    //    {
    //        $response = $this->get($this->uri);
    //
    //        $response->assertRedirect('/login');
    //    }
    //
    //    /** @test */
    //    public function test_user_call()
    //    {
    //        $this->actingAs($this->user, 'web');
    //
    //        $response = $this->get($this->uri);
    //
    //        $response->assertForbidden();
    //    }

    /** @test */
    public function test_admin_call(): void
    {
        $this->user->assignRole('admin');

        $this->actingAs($this->user, 'web');

        $response = $this->get($this->uri);

        $response->assertSuccessful();
    }
}

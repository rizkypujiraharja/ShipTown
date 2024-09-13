<?php

namespace Tests\External\Webhooks\Controllers\SubscriptionController;

use App\Modules\Webhooks\src\Services\SnsService;
use App\Modules\Webhooks\src\WebhooksServiceProviderBase;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        WebhooksServiceProviderBase::enableModule();

        SnsService::subscribeToTopic('https://test.com');

        $user = User::factory()->create();

        $this->actingAs($user, 'api');
    }

    protected function tearDown(): void
    {
        SnsService::client()->deleteTopic(['TopicArn' => SnsService::getConfiguration()->topic_arn]);

        parent::tearDown();
    }

    /** @test */
    public function test_store_call_returns_ok(): void
    {
        $response = $this->postJson(route('api.modules.webhooks.subscriptions.store'), [
            'endpoint' => 'https://test.com',
        ]);

        ray($response->json());

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'service',
                'method',
                'response' => [
                    'SubscriptionArn',
                ],
            ],
        ]);
    }
}

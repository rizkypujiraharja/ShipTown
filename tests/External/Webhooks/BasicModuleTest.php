<?php

namespace Tests\External\Webhooks;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\Webhooks\src\Jobs\PublishInventoryMovementWebhooksJob;
use App\Modules\Webhooks\src\Models\PendingWebhook;
use App\Modules\Webhooks\src\Models\WebhooksConfiguration;
use App\Modules\Webhooks\src\WebhooksServiceProviderBase;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    public function test_if_env_variables_are_set(): void
    {
        $this->assertNotEmpty(config('aws.credentials.key'), 'AWS_ACCESS_KEY_ID env var not set');
        $this->assertNotEmpty(config('aws.credentials.secret'), 'AWS_SECRET_ACCESS_KEY env var not set');
        $this->assertNotEmpty(config('aws.user_code'), 'AWS_USER_CODE env var not set');
        $this->assertNotEmpty(config('aws.region'), 'AWS_DEFAULT_REGION env var not set');

        $this->assertNotEmpty(env('AWS_DEFAULT_REGION'), 'AWS_DEFAULT_REGION env var not set');
    }

    public function test_if_publishes_inventory_movements(): void
    {
        WebhooksConfiguration::updateOrCreate([], [
            'topic_arn' => env('TEST_SNS_TOPIC_ARN'),
        ]);

        WebhooksServiceProviderBase::enableModule();

        Product::factory()->create();
        Warehouse::factory()->create();

        /** @var Inventory $inventory */
        $inventory = Inventory::first();

        InventoryMovement::query()->create([
            'inventory_id' => $inventory->id,
            'product_id' => $inventory->product_id,
            'warehouse_id' => $inventory->warehouse_id,
            'quantity_before' => 10,
            'quantity_delta' => 10,
            'quantity_after' => 10,
            'description' => 'test1',
        ]);

        InventoryMovement::query()->create([
            'inventory_id' => $inventory->id,
            'product_id' => $inventory->product_id,
            'warehouse_id' => $inventory->warehouse_id,
            'quantity_before' => 10,
            'quantity_delta' => 10,
            'quantity_after' => 10,
            'description' => 'test2',
        ]);

        PublishInventoryMovementWebhooksJob::dispatch();

        $this->assertCount(0, PendingWebhook::all());
    }
}

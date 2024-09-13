<?php

namespace Tests\Unit\Modules\Webhooks;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use App\Models\Inventory;
use App\Models\OrderProduct;
use App\Models\OrderProductShipment;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\Webhooks\src\Models\Webhook;
use App\Modules\Webhooks\src\Services\WebhooksService;
use App\Modules\Webhooks\src\WebhooksServiceProviderBase;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        WebhooksServiceProviderBase::enableModule();
    }

    /** @test */
    public function testBasicFunctionality(): void
    {
        if (empty(config('aws.credentials.secret'))) {
            $this->markTestSkipped('SQS_QUEUE is not set.');
        }

        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $inventory = Inventory::query()->firstOrCreate([
            'product_id' => $product->getKey(),
            'warehouse_id' => $warehouse->getKey(),
        ]);

        /** @var OrderProduct $orderProduct */
        $orderProduct = OrderProduct::factory()->create([
            'product_id' => $product->getKey(),
        ]);

        $orderProductShipment = OrderProductShipment::create([
            'order_id' => $orderProduct->order_id,
            'order_product_id' => $orderProduct->getKey(),
            'inventory_id' => $inventory->getKey(),
            'warehouse_id' => $warehouse->getKey(),
            'product_id' => $product->getKey(),
            'quantity_shipped' => 10,
        ]);

        $this->assertDatabaseHas('modules_webhooks_pending_webhooks', [
            'model_class' => OrderProductShipment::class,
            'model_id' => $orderProductShipment->getKey(),
        ]);

        WebhooksService::dispatchJobs();

        $webhooks = Webhook::query()->whereNull('sns_message_id')->get();

        $this->assertEmpty($webhooks);
        //        $this->assertDatabaseHas('modules_webhooks_pending_webhooks', [
        //            'sns_message_id' => 'blue',
        //        ]);
    }

    /** @test */
    public function testIfNoErrorsDuringEvents(): void
    {
        EveryMinuteEvent::dispatch();
        EveryFiveMinutesEvent::dispatch();
        EveryTenMinutesEvent::dispatch();
        EveryHourEvent::dispatch();
        EveryDayEvent::dispatch();

        $this->assertTrue(true, 'Errors encountered while dispatching events');
    }
}

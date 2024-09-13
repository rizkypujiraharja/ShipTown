<?php

namespace Tests\Unit\Modules\Automations\Actions;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Modules\Automations\src\Actions\Order\SplitBundleSkuAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SplitBundleSkuActionTest extends TestCase
{
    use RefreshDatabase;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->order = Order::factory()->create();

        /** @var Product $bundle_sku */
        $bundle_sku = Product::factory()->create(['sku' => 'BUNDLE_SKU']);
        OrderProduct::factory()->create([
            'order_id' => $this->order->getKey(),
            'product_id' => $bundle_sku->getKey(),
            'sku_ordered' => $bundle_sku->sku,
            'name_ordered' => $bundle_sku->name,
            'quantity_ordered' => 1,
            'price' => 100,
        ]);

        /** @var Product $bundle_product_1 */
        $bundle_product_1 = Product::factory()->create(['sku' => 'BUNDLE_PRODUCT_1']);
        $bundle_product_1->prices()->update([
            'price' => 75,
        ]);

        /** @var Product $bundle_product_1 */
        $bundle_product_1 = Product::factory()->create(['sku' => 'BUNDLE_PRODUCT_2']);
        $bundle_product_1->prices()->update([
            'price' => 75,
        ]);

        /** @var Product $random_sku */
        $random_sku = Product::factory()->create(['sku' => 'RANDOM_SKU']);
        OrderProduct::factory()->create([
            'order_id' => $this->order->getKey(),
            'product_id' => $random_sku->getKey(),
            'sku_ordered' => $random_sku->sku,
            'name_ordered' => $random_sku->name,
            'quantity_ordered' => 1,
            'price' => 100,
        ]);
    }

    //    public function test_successful_action()
    //    {
    //        $event = new ActiveOrderCheckEvent($this->order);
    //        $action = new SplitBundleSkuAction($event);
    //
    //        // act
    //        $actionSucceeded = $action->handle('BUNDLE_SKU,BUNDLE_PRODUCT_1,BUNDLE_PRODUCT_2');
    //
    //        // validate
    //        $this->assertTrue($actionSucceeded, 'Action failed');
    //
    //        $this->assertEquals(4, $this->order->orderProducts()->count(), 'Incorrect order products count');
    //        $this->assertEquals(200, $this->order->orderTotals()->first()->total_ordered);
    //    }

    public function test_incorrect_inputs(): void
    {
        $action = new SplitBundleSkuAction($this->order);

        $this->assertFalse($action->handle(''), 'Blank value should not be allowed');
        $this->assertFalse($action->handle('123'), 'Two SKUs should be present');
        $this->assertFalse($action->handle('123,'), 'Second SKU should not be blank');
        $this->assertFalse($action->handle('456,456'), 'Same SKUs should not be allowed');
    }
}

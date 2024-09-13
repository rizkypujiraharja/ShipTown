<?php

namespace Tests\Unit\Modules\Automations\Actions;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\Automations\src\Actions\Order\SplitOrderToWarehouseCodeAction;
use App\Modules\Automations\src\AutomationsServiceProvider;
use App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsJob;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use Tests\TestCase;

class SplitOrderToWarehouseCodeActionTest extends TestCase
{
    //    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function testExample(): void
    {
        $random_number = rand(2, 4);

        AutomationsServiceProvider::enableModule();

        $warehouses = Warehouse::factory()->count($random_number)->create();
        $products = Product::factory()->count($random_number)->create();

        $warehouses->each(function (Warehouse $warehouse) use ($products) {
            $product = $products->shift();
            Inventory::updateOrCreate([
                'product_id' => $product->getKey(),
                'warehouse_id' => $warehouse->getKey(),
            ], [
                'quantity' => 100,
                'warehouse_code' => $warehouse->code,
            ]);
        });

        $order = Order::factory()->create(['status_code' => 'split_order']);

        Product::all()->each(function (Product $product) use ($order) {
            $orderProduct = new OrderProduct;
            $orderProduct->order_id = $order->getKey();
            $orderProduct->product_id = $product->getKey();
            $orderProduct->name_ordered = $product->name;
            $orderProduct->sku_ordered = $product->sku;
            $orderProduct->price = $product->price;
            $orderProduct->quantity_ordered = 1;
            $orderProduct->save();
        });

        $warehouses->each(function (Warehouse $warehouse) {
            $status_code_name = 'packing_'.$warehouse->code;

            $automation = new Automation;
            $automation->enabled = false;
            $automation->name = 'split_order to '.$status_code_name;
            $automation->save();

            $condition = new Condition;
            $condition->automation_id = $automation->getKey();
            $condition->condition_class = StatusCodeEqualsCondition::class;
            $condition->condition_value = 'split_order';
            $condition->save();

            $action = new Action;
            $action->automation_id = $automation->getKey();
            $action->action_class = SplitOrderToWarehouseCodeAction::class;
            $action->action_value = $warehouse->code.',packing_web';
            $action->save();

            $automation->enabled = true;
            $automation->save();
        });

        RunEnabledAutomationsJob::dispatch();

        // we will have original order left + X new ones
        $this->assertEquals(1 + $random_number, Order::count());
        $this->assertEquals($random_number * 2, OrderProduct::count());
    }
}

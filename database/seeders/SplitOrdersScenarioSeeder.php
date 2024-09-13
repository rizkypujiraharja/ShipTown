<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\Automations\src\Actions\Order\SplitOrderToWarehouseCodeAction;
use App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class SplitOrdersScenarioSeeder extends Seeder
{
    /**
     * @var Collection|Model|mixed
     */
    private $sampleProducts;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensurePackingStatusExists();
        //        $this->createSampleWarehouses(3);
        $this->createWarehouseAutomations();
        $this->createSampleProducts(3);
        $this->createSampleSplitOrders(1);
        $this->createSampleSplitSingleProductsOrders(1);
    }

    private function createWarehouseAutomations(): void
    {
        Warehouse::all()->each(function (Warehouse $warehouse) {
            $automation = new Automation;
            $automation->enabled = false;
            $automation->name = 'packing to packing_'.$warehouse->code;
            $automation->save();

            $condition = new Condition;
            $condition->automation_id = $automation->getKey();
            $condition->condition_class = StatusCodeEqualsCondition::class;
            $condition->condition_value = 'packing';
            $condition->save();

            $action = new Action;
            $action->automation_id = $automation->getKey();
            $action->action_class = SplitOrderToWarehouseCodeAction::class;
            $action->action_value = $warehouse->code.',packing_'.$warehouse->code;
            $action->save();

            $automation->enabled = true;
            $automation->save();
        });
    }

    private function ensurePackingStatusExists(): void
    {
        /** @var OrderStatus $newStatus */
        $newStatus = OrderStatus::factory()->make(['name' => 'packing', 'code' => 'packing']);
        OrderStatus::firstOrCreate(['code' => 'packing'], $newStatus->toArray());
    }

    protected function createSampleProducts(int $count)
    {
        $this->sampleProducts = Product::factory()->count($count)->create();
    }

    protected function createSampleSplitOrders(int $count)
    {
        /** @var Order $order */
        $orders = Order::factory()->count($count)->make();

        $orders->each(function (Order $order) {
            $order->is_editing = true;
            $order->status_code = 'packing';
            $order->order_number .= '-SAMPLE-SPLIT';
            $order->save();

            // we will duplicate collection each time
            $warehouses = collect(Warehouse::all());

            $this->sampleProducts->each(function (Product $product) use ($order, $warehouses) {
                $warehouse = $warehouses->shift();

                Inventory::updateOrCreate([
                    'product_id' => $product->getKey(),
                    'warehouse_id' => $warehouse->getKey(),
                    'location_id' => $warehouse->code,
                ], [
                    'quantity' => 100,
                ]);

                $orderProduct = new OrderProduct;
                $orderProduct->order_id = $order->getKey();
                $orderProduct->product_id = $product->getKey();
                $orderProduct->name_ordered = $product->name;
                $orderProduct->sku_ordered = $product->sku;
                $orderProduct->price = $product->price;
                $orderProduct->quantity_ordered = 1;
                $orderProduct->save();
            });

            $order->is_editing = false;
            $order->save();
        });
    }

    protected function createSampleSplitSingleProductsOrders(int $count)
    {
        /** @var Order $order */
        $orders = Order::factory()->count($count)->make();

        $orders->each(function (Order $order) {
            $order->is_editing = true;
            $order->status_code = 'packing';
            $order->order_number .= '-SAMPLE-SPLIT';
            $order->save();

            // we will duplicate collection each time
            $warehouses = Warehouse::all();

            $this->sampleProducts->each(function (Product $product) use ($order, $warehouses) {
                $warehouse = $warehouses->first();
                Inventory::updateOrCreate([
                    'product_id' => $product->getKey(),
                    'warehouse_id' => $warehouse->getKey(),
                    'location_id' => $warehouse->code,
                ], [
                    'quantity' => 3,
                ]);

                $warehouse = $warehouses->last();
                Inventory::updateOrCreate([
                    'product_id' => $product->getKey(),
                    'warehouse_id' => $warehouse->getKey(),
                    'location_id' => $warehouse->code,
                ], [
                    'quantity' => 3,
                ]);

                $orderProduct = new OrderProduct;
                $orderProduct->order_id = $order->getKey();
                $orderProduct->product_id = $product->getKey();
                $orderProduct->name_ordered = $product->name;
                $orderProduct->sku_ordered = $product->sku;
                $orderProduct->price = $product->price;
                $orderProduct->quantity_ordered = 5;
                $orderProduct->save();
            });

            $order->is_editing = false;
            $order->save();
        });
    }
}

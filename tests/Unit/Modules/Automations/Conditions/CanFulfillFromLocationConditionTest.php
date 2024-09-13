<?php

namespace Tests\Unit\Modules\Automations\Conditions;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Warehouse;
use App\Modules\Automations\src\Conditions\Order\CanFulfillFromLocationCondition;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use App\Modules\InventoryReservations\src\EventServiceProviderBase as InventoryReservationsEventServiceProviderBase;
use Tests\TestCase;

class CanFulfillFromLocationConditionTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_can_not_fulfill_from_location_0_condition(): void
    {
        InventoryReservationsEventServiceProviderBase::enableModule();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Automation $automation */
        $automation = Automation::factory()->create([
            'enabled' => true,
            'name' => 'paid to can_fulfill',
        ]);

        /** @var Condition $condition */
        $condition = $automation->conditions()->create([
            'condition_class' => CanFulfillFromLocationCondition::class,
            'condition_value' => '0', // we are using location_id = 0 for ALL locations
        ]);

        /** @var Order $order */
        $order = Order::factory()->create([]);

        /** @var OrderProduct $orderProduct */
        $orderProduct = OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        $orderProduct->product
            ->inventory()
            ->where(['warehouse_code' => $warehouse->code])
            ->update(['quantity' => $orderProduct->quantity_ordered - 1]);

        $this->assertFalse($condition->isTrue($order));
    }

    /**
     * A basic feature test example.
     */
    public function test_can_fulfill_from_location_0_condition(): void
    {
        InventoryReservationsEventServiceProviderBase::enableModule();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Automation $automation */
        $automation = Automation::factory()->create([
            'enabled' => true,
            'name' => 'paid to can_fulfill',
        ]);

        /** @var Condition $condition */
        $condition = $automation->conditions()->create([
            'condition_class' => CanFulfillFromLocationCondition::class,
            'condition_value' => '0', // we are using location_id = 0 for ALL locations
        ]);

        /** @var Order $order */
        $order = Order::factory()->create([]);

        /** @var OrderProduct $orderProduct */
        $orderProduct = OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        $orderProduct->product
            ->inventory()
            ->where(['warehouse_code' => $warehouse->code])
            ->update(['quantity' => 100]);

        $this->assertTrue($condition->isTrue($order));
    }

    /**
     * A basic feature test example.
     */
    public function testCanNotFulfillCondition(): void
    {
        InventoryReservationsEventServiceProviderBase::enableModule();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Automation $automation */
        $automation = Automation::factory()->create([
            'enabled' => true,
            'name' => 'paid to can_fulfill',
        ]);

        /** @var Condition $condition */
        $condition = $automation->conditions()->create([
            'condition_class' => CanFulfillFromLocationCondition::class,
            'condition_value' => $warehouse->code,
        ]);

        /** @var Order $order */
        $order = Order::factory()->create([]);

        /** @var OrderProduct $orderProduct */
        $orderProduct = OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        $orderProduct->product
            ->inventory()
            ->where(['warehouse_code' => $warehouse->code])
            ->update(['quantity' => 0]);

        $this->assertFalse($condition->isTrue($order));
    }

    /**
     * A basic feature test example.
     */
    public function test_can_fulfill_valid_condition(): void
    {
        InventoryReservationsEventServiceProviderBase::enableModule();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Automation $automation */
        $automation = Automation::factory()->create([
            'enabled' => true,
            'name' => 'paid to can_fulfill',
        ]);

        /** @var Condition $condition */
        $condition = $automation->conditions()->create([
            'condition_class' => CanFulfillFromLocationCondition::class,
            'condition_value' => $warehouse->code,
        ]);

        /** @var Order $order */
        $order = Order::factory()->create([]);

        /** @var OrderProduct $orderProduct */
        $orderProduct = OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        $orderProduct->product
            ->inventory()
            ->where(['warehouse_code' => $warehouse->code])
            ->update(['quantity' => 100]);

        $this->assertTrue($condition->isTrue($order));
    }
}

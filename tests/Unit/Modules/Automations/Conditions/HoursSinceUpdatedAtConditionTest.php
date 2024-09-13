<?php

namespace Tests\Unit\Modules\Automations\Conditions;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderProductTotal;
use App\Modules\Automations\src\Conditions\Order\HoursSinceLastUpdatedAtCondition;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HoursSinceUpdatedAtConditionTest extends TestCase
{
    public function testConditionTrue(): void
    {
        $select = DB::select('select now() as now');

        Order::factory()->create(['updated_at' => Carbon::createFromTimeString($select[0]->now)->subHours(3)]);

        $query = Order::query();

        HoursSinceLastUpdatedAtCondition::addQueryScope($query, '2');

        ray(Order::all()->toArray());
        ray(OrderProduct::all()->toArray());
        ray(OrderProductTotal::all()->toArray());

        ray($query->toSql(), $query->getBindings());

        $this->assertEquals(1, $query->count(), 'We expect 1 order to be returned');
    }

    public function testConditionFalse(): void
    {
        $select = DB::select('select now() as now');

        Order::factory()->create(['updated_at' => Carbon::createFromTimeString($select[0]->now)->subHour()]);

        $query = Order::query();

        HoursSinceLastUpdatedAtCondition::addQueryScope($query, '2');

        ray(Order::all()->toArray());

        ray($query->toSql(), $query->getBindings());

        $this->assertEquals(0, $query->count(), 'We expect 0 orders to be returned');
    }

    public function testEmptyInput(): void
    {
        Order::factory()->create();

        $query = Order::query();

        HoursSinceLastUpdatedAtCondition::addQueryScope($query, '');

        ray(Order::all()->toArray());

        ray($query->toSql(), $query->getBindings());

        $this->assertEquals(0, $query->count(), 'We expect 0 orders to be returned');
    }

    public function testInvalidInput(): void
    {
        Order::factory()->create();

        $query = Order::query();

        HoursSinceLastUpdatedAtCondition::addQueryScope($query, 'string');

        ray(Order::all()->toArray());

        ray($query->toSql(), $query->getBindings());

        $this->assertEquals(0, $query->count(), 'We expect 0 orders to be returned');
    }
}

<?php

namespace Tests\External\DpdUk;

use App\Models\Order;
use App\Modules\DpdUk\src\Models\Connection;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NextDayShippingServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     *
     * @throws Exception
     */
    public function testExample(): void
    {
        //        $connection = factory(Connection::class)->create([
        //            'username' => 'test',
        //            'password' => 'test',
        //            'account_number' => 'test',
        //        ]);
        //
        //        $service = new NextDayShippingService();
        //
        //        $order = factory(Order::class)->create();
        //
        //        $service->ship($order->getKey());
    }
}

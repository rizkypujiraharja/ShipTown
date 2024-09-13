<?php

namespace Database\Seeders;

use App\Models\NavigationMenu;
use App\Models\Order;
use Illuminate\Database\Seeder;

class OrdersStorePickupDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menu = [
            [
                'name' => 'Status: store_pickup',
                'url' => '/autopilot/packlist?inventory_source_location_id=1&status=store_pickup&address_label_template=address_label',
                'group' => 'packlist',
            ],
        ];

        NavigationMenu::insert($menu);

        Order::factory()->count(10)
            ->with('orderProducts', 4)
            ->create(['status_code' => 'store_pickup', 'shipping_method_code' => 'store_pickup']);

        Order::all()->each(function (Order $order) {
            $order->total_paid = $order->total_order;
            $order->save();
        });
    }
}

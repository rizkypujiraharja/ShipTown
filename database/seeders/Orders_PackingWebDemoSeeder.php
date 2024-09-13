<?php

namespace Database\Seeders;

use App\Models\NavigationMenu;
use App\Models\Order;
use Illuminate\Database\Seeder;

class Orders_PackingWebDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menu = [
            [
                'name' => 'Status: packing_web',
                'url' => '/autopilot/packlist?inventory_source_location_id=100&status=packing_web&is_picked=true&sort=order_placed_at%2Cproduct_line_count%2Ctotal_quantity_ordered',
                'group' => 'packlist',
            ],
        ];

        NavigationMenu::insert($menu);

        Order::factory()->count(10)
            ->with('orderProducts', 4)
            ->create(['status_code' => 'packing_web']);

        Order::all()->each(function (Order $order) {
            $order->total_paid = $order->total_order;
            $order->save();
        });
    }
}

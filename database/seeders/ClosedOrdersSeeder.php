<?php

namespace Database\Seeders;

use App\Models\Order;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ClosedOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $count = rand(15, 25);

        do {
            $orderClosedAt = Carbon::now()->subDays(rand(0, 7));
            Order::factory()
                ->with('orderProducts', rand(1, 4))
                ->create([
                    'order_closed_at' => $orderClosedAt,
                    'order_placed_at' => $orderClosedAt->subDays(rand(1, 4)),
                    'packed_at' => $orderClosedAt,
                    'packer_user_id' => User::inRandomOrder()->first('id')->getKey(),
                    'status_code' => 'complete',
                ]);
            $count--;
        } while ($count > 0);
    }
}

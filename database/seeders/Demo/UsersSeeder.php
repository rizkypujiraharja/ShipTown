<?php

namespace Database\Seeders\Demo;

use App\Models\Warehouse;
use App\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var User $admin */
        $admin = User::query()->firstOrCreate([
            'email' => 'demo-admin@ship.town',
        ], [
            'name' => 'Artur Hanusek',
            'warehouse_id' => Warehouse::firstOrCreate(['code' => 'DUB'], ['name' => 'Dublin'])->getKey(),
            'warehouse_code' => Warehouse::firstOrCreate(['code' => 'DUB'], ['name' => 'Dublin'])->code,
            'password' => bcrypt('secret1144'),
            'ask_for_shipping_number' => false,
        ]);
        $admin->assignRole(Role::findOrCreate('admin'));

        /** @var User $user */
        $user = User::query()->firstOrCreate([
            'email' => 'demo-user@ship.town',
        ], [
            'name' => 'Joni Melabo',
            'warehouse_id' => Warehouse::firstOrCreate(['code' => 'GAL'], ['name' => 'Galway'])->getKey(),
            'warehouse_code' => Warehouse::firstOrCreate(['code' => 'GAL'], ['name' => 'Galway'])->code,
            'password' => bcrypt('secret1144'),
        ]);
        $user->assignRole(Role::findOrCreate('user'));
    }
}

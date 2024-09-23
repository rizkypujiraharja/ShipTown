<?php

namespace Database\Factories;

use App\Models\Warehouse;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        $warehouse = Warehouse::query()->inRandomOrder()->first() ?? Warehouse::factory()->create();

        $email = $this->faker->unique()->safeEmail();

        while (User::withTrashed()->where(['email' => $email])->exists()) {
            $email = $this->faker->unique()->safeEmail();
        }

        return [
            'name' => $this->faker->firstName().' '.$this->faker->lastName(),
            'warehouse_id' => $warehouse->getKey(),
            'warehouse_code' => $warehouse->code,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => bcrypt('secret123'), // password
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): UserFactory
    {
        return $this->afterCreating(function ($user) {
            $user->assignRole('admin');
        })->state([]);
    }
}

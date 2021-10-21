<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDatetimeColumnsOnOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            DB::statement('ALTER TABLE `orders` CHANGE `order_placed_at` `order_placed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;');
            DB::statement('ALTER TABLE `orders` CHANGE `order_closed_at` `order_closed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;');
        });
    }
}
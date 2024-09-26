<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules_reports_inventory_dashboard_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id');
            $table->string('warehouse_code');
            $table->integer('missing_restock_levels')->default(0);
            $table->integer('wh_products_available')->default(0);
            $table->integer('wh_products_out_of_stock')->default(0);
            $table->integer('wh_products_required')->default(0);
            $table->integer('wh_products_incoming')->default(0);
            $table->integer('wh_products_stock_level_ok')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['warehouse_id', 'warehouse_code'], 'warehouse_id_warehouse_code_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules_reports_inventory_dashboard_records');
    }
};

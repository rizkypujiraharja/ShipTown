<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_collection_records', function (Blueprint $table) {
            $table->dropForeign(['product_sku']);
        });

        Schema::table('data_collection_records', function (Blueprint $table) {
            $table->dropColumn('product_sku');
        });
    }
};

<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EnsureDataCollectionRecordsTableDoesntHaveProductSkuTest extends TestCase
{
    /** @test */
    public function testIfColumnIsMissing(): void
    {
        // product_sku can be updated in the future
        // below tables might hold thousands of records
        // so we need to make sure that we don't have product_sku column
        // as during sku update we will need to update all records
        // and it might take a lot of time and slow down the system
        $this->assertFalse(Schema::hasColumn('data_collection_records', 'product_sku'), 'Column product_sku not allowed on data_collection_records table');
        $this->assertFalse(Schema::hasColumn('inventory_movements', 'product_sku'), 'Column product_sku not allowed on inventory_movements table');

    }
}

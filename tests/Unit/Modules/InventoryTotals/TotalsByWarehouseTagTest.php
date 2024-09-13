<?php

namespace Tests\Unit\Modules\InventoryTotals;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryTotals\src\InventoryTotalsServiceProvider;
use App\Modules\InventoryTotals\src\Jobs\EnsureInventoryTotalsByWarehouseTagRecordsExistJob;
use App\Modules\InventoryTotals\src\Jobs\RecalculateInventoryTotalsByWarehouseTagJob;
use App\Modules\InventoryTotals\src\Models\Configuration;
use App\Modules\InventoryTotals\src\Models\InventoryTotalByWarehouseTag;
use App\Services\InventoryService;
use Spatie\Tags\Tag;
use Tests\TestCase;

class TotalsByWarehouseTagTest extends TestCase
{
    /** @test */
    public function test_UpdateTotalsByWarehouseTagTableJob(): void
    {
        InventoryTotalsServiceProvider::enableModule();

        $warehouse1_withTag = Warehouse::factory()->create();
        $warehouse1_withTag->attachTag('test_tag');

        $warehouse2_withTag = Warehouse::factory()->create();
        $warehouse2_withTag->attachTag('test_tag');

        $warehouse3_noTag = Warehouse::factory()->create();

        $product = Product::factory()->create();

        $inventory1_withTag = Inventory::query()->where([
            'product_id' => $product->getKey(),
            'warehouse_id' => $warehouse1_withTag->getKey(),
        ])->first();

        $inventory2_withTag = Inventory::query()->where([
            'product_id' => $product->getKey(),
            'warehouse_id' => $warehouse2_withTag->getKey(),
        ])->first();

        $inventory3_noTag = Inventory::query()->where([
            'product_id' => $product->getKey(),
            'warehouse_id' => $warehouse3_noTag->getKey(),
        ])->first();

        InventoryService::adjust($inventory1_withTag, 10);
        InventoryService::adjust($inventory2_withTag, 5);
        InventoryService::adjust($inventory3_noTag, 3);

        InventoryTotalByWarehouseTag::query()->update([
            'quantity' => 0,
            'max_inventory_updated_at' => '2000-01-01 00:00:00',
        ]);

        RecalculateInventoryTotalsByWarehouseTagJob::dispatch();

        ray('inventory_totals_by_warehouse_tag', InventoryTotalByWarehouseTag::query()->first()->toArray());

        $this->assertDatabaseHas('inventory_totals_by_warehouse_tag', [
            'tag_id' => Tag::findFromString('test_tag')->getKey(),
            'product_id' => $product->getKey(),
            'quantity' => 15,
        ]);

        $this->assertDatabaseMissing('inventory_totals_by_warehouse_tag', [
            'calculated_at' => null,
        ]);
    }

    /** @test */
    public function test_EnsureTotalsByWarehouseTagRecordsExistJob(): void
    {
        InventoryTotalsServiceProvider::enableModule();

        $warehouse1_withTag = Warehouse::factory()->create();
        $warehouse1_withTag->attachTag('test_tag');

        $product = Product::factory()->create();

        InventoryTotalByWarehouseTag::query()->forceDelete();
        Configuration::query()->forceDelete();

        EnsureInventoryTotalsByWarehouseTagRecordsExistJob::dispatch();

        $this->assertDatabaseHas('inventory_totals_by_warehouse_tag', [
            'tag_id' => Tag::findFromString('test_tag')->getKey(),
            'product_id' => $product->getKey(),
        ]);
    }

    /** @test */
    public function test_basic_scenario(): void
    {
        InventoryTotalsServiceProvider::enableModule();

        $warehouse1_withTag = Warehouse::factory()->create();
        $warehouse1_withTag->attachTag('test_tag');

        $warehouse2_withTag = Warehouse::factory()->create();
        $warehouse2_withTag->attachTag('test_tag');

        $warehouse3_noTag = Warehouse::factory()->create();

        $product = Product::factory()->create();

        $inventory1_withTag = Inventory::query()->where([
            'product_id' => $product->getKey(),
            'warehouse_id' => $warehouse1_withTag->getKey(),
        ])->first();

        $inventory2_withTag = Inventory::query()->where([
            'product_id' => $product->getKey(),
            'warehouse_id' => $warehouse2_withTag->getKey(),
        ])->first();

        $inventory3_noTag = Inventory::query()->where([
            'product_id' => $product->getKey(),
            'warehouse_id' => $warehouse3_noTag->getKey(),
        ])->first();

        InventoryService::adjust($inventory1_withTag, 10);
        InventoryService::adjust($inventory2_withTag, 5);
        InventoryService::adjust($inventory3_noTag, 3);

        ray('inventory_totals_by_warehouse_tag', InventoryTotalByWarehouseTag::query()->first()->toArray());

        $this->assertDatabaseHas('inventory_totals_by_warehouse_tag', [
            'tag_id' => Tag::findFromString('test_tag')->getKey(),
            'product_id' => $product->getKey(),
            'quantity' => 15,
        ]);
    }

    /** @test */
    public function test_module_basic_functionality(): void
    {
        InventoryTotalsServiceProvider::enableModule();

        $warehouse = Warehouse::factory()->create();
        $warehouse->attachTag('test_tag');

        $product = Product::factory()->create();

        ray('warehouses', Warehouse::query()->get()->toArray());
        ray('products', Product::query()->get()->toArray());
        ray('inventory_totals_by_warehouse_tag', InventoryTotalByWarehouseTag::query()->first()->toArray());

        $this->assertDatabaseHas('inventory_totals_by_warehouse_tag', [
            'tag_id' => Tag::findFromString('test_tag')->getKey(),
            'product_id' => $product->getKey(),
            'quantity' => 0,
        ]);
    }
}

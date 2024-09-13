<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductAlias;
use Illuminate\Support\Facades\Log;

/**
 * Class ProductService.
 */
class ProductService
{
    public static function find(string $skuOrAlias): ?Product
    {
        /** @var Product $product */
        $product = Product::findBySKU($skuOrAlias);

        if ($product) {
            return $product;
        }

        return self::findByAlias($skuOrAlias);
    }

    public static function findByAlias(string $alias): ?Product
    {
        /** @var ProductAlias $productAlias */
        $productAlias = ProductAlias::query()->where(['alias' => $alias])->with('product')->first();

        if ($productAlias) {
            return $productAlias->product()->first();
        }

        return null;
    }

    public static function reserve(string $sku, float $quantity, string $message): bool
    {
        $aProduct = Product::query()->where(['sku' => $sku])->first();

        if ($aProduct) {
            $aProduct->increment('quantity_reserved', $quantity);

            return true;
        }

        Log::warning('Could not reserve quantity - SKU does not exist', ['sku' => $sku]);

        return false;
    }

    public static function release(string $sku, float $quantity, string $message): bool
    {
        $aProduct = Product::query()->where(['sku' => $sku])->first();

        if ($aProduct) {
            $aProduct->decrement('quantity_reserved', $quantity);

            return true;
        }

        Log::warning('Could not release quantity - SKU does not exist', ['sku' => $sku]);

        return false;
    }

    public static function merge(string $skuToKeep, string $skuToMerge)
    {
        /** @var Product $productToKeep */
        $productToKeep = Product::findBySKU($skuToKeep);

        /** @var Product $productToMerge */
        $productToMerge = Product::findBySKU($skuToMerge);

        // Merge productToMerge into product
        $productToMerge->update(['sku' => 'merged_to_'.$skuToKeep.'_'.time()]);
        $productToMerge->aliases()->update(['product_id' => $productToKeep->id]);
        $productToMerge->tags()->delete();

        ProductAlias::query()->updateOrCreate(['alias' => $skuToMerge], ['product_id' => $productToKeep->id]);

        Inventory::query()
            ->where(['product_id' => $productToMerge->id])
            ->where('quantity_available', '!=', 0)
            ->get()
            ->each(function (Inventory $productToMergeInventory) use ($productToKeep, $productToMerge) {
                $productToKeepInventory = Inventory::query()
                    ->where([
                        'product_id' => $productToKeep->id,
                        'warehouse_id' => $productToMergeInventory->warehouse_id,
                    ])
                    ->first();

                InventoryService::adjust($productToKeepInventory, $productToMergeInventory->quantity, [
                    'description' => 'merging from "'.$productToMerge->sku.'"',
                ]);

                InventoryService::adjust($productToMergeInventory, -$productToMergeInventory->quantity, [
                    'description' => 'merging to "'.$productToKeep->sku.'"',
                ]);

                $productToMergeInventory->update(['quantity_reserved' => 0]);
            });
    }
}

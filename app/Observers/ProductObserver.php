<?php

namespace App\Observers;

use App\Events\Product\ProductCreatedEvent;
use App\Events\Product\ProductUpdatedEvent;
use App\Models\Product;
use App\Models\ProductAlias;
use Exception;

class ProductObserver
{
    /**
     * Handle the product "created" event.
     */
    public function created(Product $product): void
    {
        ProductCreatedEvent::dispatch($product);
    }

    /**
     * Handle the product "updated" event.
     *
     *
     *
     * @throws Exception
     */
    public function updated(Product $product): void
    {
        $this->upsertProductAliasRecords($product);

        ProductUpdatedEvent::dispatch($product);
    }

    private function upsertProductAliasRecords(Product $product): void
    {
        ProductAlias::updateOrCreate(
            ['alias' => $product->sku],
            ['product_id' => $product->id]
        );
    }
}

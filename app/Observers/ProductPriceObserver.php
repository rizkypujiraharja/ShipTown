<?php

namespace App\Observers;

use App\Events\Product\ProductPriceUpdatedEvent;
use App\Models\ProductPrice;

class ProductPriceObserver
{
    /**
     * Handle the product "updated" event.
     */
    public function updated(ProductPrice $product_price): void
    {
        $pricingChanged = $product_price->isAnyAttributeChanged([
            'price',
            'sale_price',
            'sale_price_start_date',
            'sale_price_end_date',
        ]);

        if ($pricingChanged) {
            ProductPriceUpdatedEvent::dispatch($product_price);
        }
    }
}

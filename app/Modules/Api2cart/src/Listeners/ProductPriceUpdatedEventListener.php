<?php

namespace App\Modules\Api2cart\src\Listeners;

use App\Events\Product\ProductPriceUpdatedEvent;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\Modules\Api2cart\src\Models\Api2cartProductLink;

class ProductPriceUpdatedEventListener
{
    /**
     * Handle the event.
     */
    public function handle(ProductPriceUpdatedEvent $event): void
    {
        $product_price = $event->product_price;

        if ($product_price->product->doesNotHaveTags(['Available Online'])) {
            return;
        }

        if (Api2cartConnection::where(['pricing_source_warehouse_id' => $product_price->warehouse_id])->exists()) {
            Api2cartProductLink::query()
                ->where(['product_id' => $product_price->product_id])
                ->update(['is_in_sync' => false]);
        }
    }
}

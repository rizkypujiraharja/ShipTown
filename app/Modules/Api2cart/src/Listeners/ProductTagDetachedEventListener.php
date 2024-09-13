<?php

namespace App\Modules\Api2cart\src\Listeners;

use App\Events\Product\ProductTagDetachedEvent;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\Modules\Api2cart\src\Models\Api2cartProductLink;

class ProductTagDetachedEventListener
{
    /**
     * Handle the event.
     */
    public function handle(ProductTagDetachedEvent $event): void
    {
        if ($event->tag() === 'Available Online') {
            Api2cartConnection::query()
                ->get()
                ->each(function (Api2cartConnection $connection) use ($event) {
                    Api2cartProductLink::query()
                        ->where([
                            'product_id' => $event->product()->id,
                            'api2cart_connection_id' => $connection->id,
                        ])
                        ->delete();
                });
        }
    }
}

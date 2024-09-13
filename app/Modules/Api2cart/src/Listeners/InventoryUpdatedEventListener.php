<?php

namespace App\Modules\Api2cart\src\Listeners;

use App\Events\Inventory\InventoryUpdatedEvent;
use App\Models\Warehouse;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\Modules\Api2cart\src\Models\Api2cartProductLink;
use Illuminate\Support\Arr;

class InventoryUpdatedEventListener
{
    /**
     * Handle the event.
     */
    public function handle(InventoryUpdatedEvent $event): void
    {
        if ($event->inventory->product->doesNotHaveTags(['Available Online'])) {
            return;
        }

        if ($this->warehouseHasRequiredTags($event->inventory->warehouse)) {
            return;
        }

        Api2cartProductLink::query()
            ->where(['product_id' => $event->inventory->product_id])
            ->update(['is_in_sync' => false]);
    }

    private function warehouseHasRequiredTags(Warehouse $warehouse): bool
    {
        /** @var Api2cartConnection $api2cartProductLink */
        $api2cartConnection = Api2cartConnection::query()->first();

        $requiredTags = Arr::wrap($api2cartConnection->inventory_source_warehouse_tag);

        return $warehouse->doesNotHaveTags($requiredTags);
    }
}

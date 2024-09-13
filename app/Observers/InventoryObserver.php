<?php

namespace App\Observers;

use App\Events\Inventory\InventoryUpdatedEvent;
use App\Models\Inventory;

class InventoryObserver
{
    public function updating(Inventory $inventory)
    {
        $newQuantity = $inventory->quantity;
        $oldQuantity = $inventory->getOriginal('quantity');

        if ($newQuantity === $oldQuantity) {
            return;
        }

        $inventory->last_movement_at = now();

        if ($newQuantity > $oldQuantity) {
            $inventory->last_received_at = now();
            $inventory->first_received_at = $inventory->first_received_at ?? now();
        }

        if ($newQuantity < $oldQuantity) {
            $inventory->last_sold_at = now();
            $inventory->first_sold_at = $inventory->first_sold_at ?? now();
        }
    }

    public function updated(Inventory $inventory): void
    {
        InventoryUpdatedEvent::dispatch($inventory);
    }
}

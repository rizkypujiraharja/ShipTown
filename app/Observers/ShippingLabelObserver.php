<?php

namespace App\Observers;

use App\Events\ShippingLabel\ShippingLabelCreatedEvent;
use App\Models\ShippingLabel;

class ShippingLabelObserver
{
    public function created(ShippingLabel $shippingLabel): void
    {
        ShippingLabelCreatedEvent::dispatch($shippingLabel);
    }
}

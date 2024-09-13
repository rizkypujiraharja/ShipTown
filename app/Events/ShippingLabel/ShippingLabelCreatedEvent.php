<?php

namespace App\Events\ShippingLabel;

use App\Models\ShippingLabel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShippingLabelCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ShippingLabel $shippingLabel;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ShippingLabel $shippingLabel)
    {
        $this->shippingLabel = $shippingLabel;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('channel-name');
    }
}

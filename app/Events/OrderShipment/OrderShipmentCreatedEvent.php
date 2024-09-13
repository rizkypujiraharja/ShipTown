<?php

namespace App\Events\OrderShipment;

use App\Models\OrderShipment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderShipmentCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public OrderShipment $orderShipment;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(OrderShipment $orderShipment)
    {
        $this->orderShipment = $orderShipment;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('channel-name');
    }
}

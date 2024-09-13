<?php

namespace App\Events\OrderStatus;

use App\Models\OrderStatus;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public OrderStatus $orderStatus;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(OrderStatus $orderStatus)
    {
        $this->orderStatus = $orderStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('channel-name');
    }
}

<?php

namespace App\Events\OrderProduct;

use App\Models\OrderProduct;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class OrderProductCreatedEvent
 */
class OrderProductCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public OrderProduct $orderProduct;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(OrderProduct $orderProduct)
    {
        $this->orderProduct = $orderProduct;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('channel-name');
    }
}

<?php

namespace App\Events\PrintJob;

use App\Modules\PrintNode\src\Models\PrintJob;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrintJobCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PrintJob $printJob;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(PrintJob $printJob)
    {
        $this->printJob = $printJob;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(): array
    {
        return new PrivateChannel('channel-name');
    }
}

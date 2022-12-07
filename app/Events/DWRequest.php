<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DWRequest implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $to,$pendingCount;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($to,$pendingCount)
    {
        $this->to = $to;
        $this->pendingCount = $pendingCount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('fe-main.'.$this->to);
    }

    public function broadcastAs()
    {
        return 'dwreq';
    }

    public function broadcastWith()
    {
        return ['pending_count' => $this->pendingCount];
    }
}

<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PromoNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $to,$optionPromo;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($to,$optionPromo)
    {
        $this->to = $to;
        $this->optionPromo = $optionPromo;
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
        return 'promoNotif';
    }

    public function broadcastWith()
    {
        return ['option_promo' => $this->optionPromo];
    }
}

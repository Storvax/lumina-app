<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReacted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reactionData;
    public $roomId;

    public function __construct($roomId, $reactionData)
    {
        $this->roomId = $roomId;
        $this->reactionData = $reactionData;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('chat.' . $this->roomId),
        ];
    }
    public function broadcastWith(): array
    {
        return $this->reactionData;
    }
}
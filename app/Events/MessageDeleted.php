<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $messageId;

    public function __construct($roomId, $messageId)
    {
        $this->roomId = $roomId;
        $this->messageId = $messageId;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('chat.' . $this->roomId),
        ];
    }
}
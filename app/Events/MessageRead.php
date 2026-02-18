<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageIds;
    public $roomId;
    public $userId;

    public function __construct($roomId, $messageIds, $userId)
    {
        $this->roomId = $roomId;
        $this->messageIds = $messageIds;
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
        return new Channel('chat.' . $this->roomId);
    }
}
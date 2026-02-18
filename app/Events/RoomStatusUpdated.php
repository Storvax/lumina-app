<?php

namespace App\Events;

use App\Models\Room;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $room;
    public $status; // 'normal' ou 'crisis'

    public function __construct(Room $room)
    {
        $this->room = $room;
        $this->status = $room->is_crisis_mode ? 'crisis' : 'normal';
    }

    public function broadcastOn()
    {
        return new Channel('chat.' . $this->room->id);
    }
}
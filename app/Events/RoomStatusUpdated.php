<?php

namespace App\Events;

use App\Models\Room;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $status; // 'normal' ou 'crisis'
    private $roomId;

    public function __construct(Room $room)
    {
        $this->roomId = $room->id;
        $this->status = $room->is_crisis_mode ? 'crisis' : 'normal';
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('chat.' . $this->roomId),
        ];
    }

    public function broadcastWith(): array
    {
        return ['status' => $this->status];
    }
}
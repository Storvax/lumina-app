<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::all();

        // Endpoint invisível para o "Live Polling" do Frontend
        if ($request->ajax() || $request->wantsJson()) {
            $stats = [];
            foreach ($rooms as $room) {
                $stats[$room->id] = DB::table('room_visits')
                    ->where('room_id', $room->id)
                    ->where('updated_at', '>=', now()->subMinutes(15))
                    ->count();
            }
            return response()->json($stats);
        }
        
        return view('rooms.index', compact('rooms'));
    }
}
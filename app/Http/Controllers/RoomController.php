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
            $counts = DB::table('room_visits')
                ->whereIn('room_id', $rooms->pluck('id'))
                ->where('updated_at', '>=', now()->subMinutes(15))
                ->groupBy('room_id')
                ->select('room_id', DB::raw('count(*) as total'))
                ->pluck('total', 'room_id');

            $stats = $rooms->pluck('id')->mapWithKeys(
                fn($id) => [$id => $counts->get($id, 0)]
            );

            return response()->json($stats);
        }
        
        return view('rooms.index', compact('rooms'));
    }
}
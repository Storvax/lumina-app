<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        // Excluir salas privadas (buddy sessions) da listagem pública.
        $rooms = Room::where('is_private', false)->where('is_active', true)->get();

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
        
        $initialStats = DB::table('room_visits')
            ->whereIn('room_id', $rooms->pluck('id'))
            ->where('updated_at', '>=', now()->subMinutes(15))
            ->groupBy('room_id')
            ->select('room_id', DB::raw('count(*) as total'))
            ->pluck('total', 'room_id');

        return view('rooms.index', compact('rooms', 'initialStats'));
    }
}
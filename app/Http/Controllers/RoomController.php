<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    /**
     * Renders the silent room view. Presence is managed client-side
     * via the `silent-room` broadcast channel defined in channels.php.
     */
    public function silentRoom()
    {
        return view('rooms.silent');
    }

    /**
     * Lists public active rooms with live presence counts.
     * Returns JSON for AJAX polling requests, HTML for initial page load.
     */
    public function index(Request $request)
    {
        $rooms = Room::publicActive()->get();

        $presenceCounts = DB::table('room_visits')
            ->whereIn('room_id', $rooms->pluck('id'))
            ->where('updated_at', '>=', now()->subMinutes(15))
            ->groupBy('room_id')
            ->select('room_id', DB::raw('count(*) as total'))
            ->pluck('total', 'room_id');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(
                $rooms->pluck('id')->mapWithKeys(fn ($id) => [$id => $presenceCounts->get($id, 0)])
            );
        }

        return view('rooms.index', [
            'rooms' => $rooms,
            'initialStats' => $presenceCounts,
        ]);
    }
}

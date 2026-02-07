<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        // Vai buscar todas as salas à base de dados
        $rooms = Room::all();
        
        return view('rooms.index', compact('rooms'));
    }
}
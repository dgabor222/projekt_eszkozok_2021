<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $id = Auth::id();
        $waiting_games = Game::where('status', '=', 'WAITING')->get();
        $current_game = User::find($id)->currentGame();
        return view('home', [
            'current_game' => $current_game,
            'waiting_games' => $waiting_games,
        ]);
    }
}

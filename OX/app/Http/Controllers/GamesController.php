<?php

namespace App\Http\Controllers;

use App\Events\StepEvent;
use App\Models\Game;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class GamesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();
        $current_game = $user->currentGame();
        return view('games.create', ['user' => $user, 'current_game' => $current_game]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id = Auth::id();
        $res = User::find($id)->createGame();
        if (! $res) {
            return abort(500);
        }
        $game = User::find($id)->currentGame();
        $request->session()->flash('game-created');
        return redirect()->route('games.show', ['game' => $game]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Game  $game
     * @return \Illuminate\Http\Response
     */
    public function show(Game $game) {
        return view('games.show', ['game' => $game]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Game  $game
     * @return \Illuminate\Http\Response
     */
    public function edit(Game $game)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Game  $game
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Game $game)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Game  $game
     * @return \Illuminate\Http\Response
     */
    public function destroy(Game $game)
    {
        //
    }

    public function fetch($id) {
        $game = Game::find($id);
        if (! $game) {
            return response()->json(['error' => 'A játék nem található'], 404);
        }
        return response()->json([
            'map' =>  $game->getMapAsMatrix(),
            'steps' => $game->steps,
            'status' => $game->status,
        ], 200);
    }

    public function step(Request $request) {
        $row = $request->row;
        $col = $request->col;
        //---------------
        $id = Auth::id();
        $game = User::find($id)->currentGame();
        $res = User::find($id)->step($row,$col);
        $win = false;
        $tie = false;
        if ($res !== true) {
            if (is_string($res)) {
                if ($res === 'GameWinned') {
                    $win = true;
                } else if ($res === 'GameTied') {
                    $tie = true;
                } else {
                    return response()->json([
                        'error' => $res,
                    ], 400);
                }
            } else {
                error_log($res);
                // stringnek vagy boolnak kéne jönni
                return abort(500);
            }
        }
        $game->refresh();
        $map = $game->getMapAsMatrix();
        $steps = $game->steps;
        broadcast(new StepEvent($game->id))->toOthers();
        return response()->json([
            'map' => $map,
            'steps' => $steps,
            'win' => $win,
            'tie' => $tie,
        ], 200);
    }

    public function join($id) {
        $user_id = Auth::id();
        $game = Game::find($id);
        if (! $game) {
            return abort(404);
        }
        $res = User::find($user_id)->joinGame($game);
        if ($res !== true) {
            if (is_string($res)) {
                return response()->json([
                    'error' => $res,
                ], 400);
            } else {
                error_log($res);
                // stringnek vagy boolnak kéne jönni
                return abort(500);
            }
        }
        $game->start();
        return redirect()->route('games.show', ['game' => $id]);
    }
}

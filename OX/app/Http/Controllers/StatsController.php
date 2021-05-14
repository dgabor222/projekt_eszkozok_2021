<?php

namespace App\Http\Controllers;

use App\Models\User;

class StatsController extends Controller
{
    public function index() {
        $users = User::paginate(10);
        return view('stats.index', ['users' => $users]);
    }
}

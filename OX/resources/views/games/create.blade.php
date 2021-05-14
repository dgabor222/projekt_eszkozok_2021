@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Új játék</h1>

    @if ($current_game !== null)
        <div class="alert alert-danger" role="alert">
            Jelenleg játékban vagy, ezért nem tudsz új játékot létrehozni!
        </div>
        <p>Kérjük, hogy térj vissza a játékba erre a linkre kattintva:</p>
        <p>
            <a href="{{ route('games.show', ['game' => $current_game]) }}">
                {{ route('games.show', ['game' => $current_game]) }}
            </a>
        </p>
    @else
        <form action="{{ route('games.store') }}" method="POST">
            @csrf
            <p>Ha létre szeretnél hozni egy új játékot, akkor kattints az alábbi gombra!</p>
            <button type="submit" class="btn btn-success">Új játék</button>
        </form>
    @endif
</div>
@endsection

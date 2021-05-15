@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Üdv az Amőbában!</h1>
    <p>Ez az app egy egyszerű amőba játékot valósít meg, amit a Projekt eszközök tárgy keretein belül csináltunk.</p>

    @if ($current_game !== null)
        <div class="alert alert-danger" role="alert">
            Figyelem! Jelenleg játékban vagy! Kérjük, hogy térj vissza a játékba erre a linkre kattintva: <a href="{{ route('games.show', ['game' => $current_game]) }}">
                {{ route('games.show', ['game' => $current_game]) }}
            </a>
        </div>
    @else
        <h3>Nyitott játékok</h3>
        <p>Ezekhez a játékokhoz jelenleg egy ember van csatlakozva, így ha a rákattintasz a Csatlakozás gombra, akkor meglesz a létszám, és a játék el tud kezdődni.</p>
        @if (count($waiting_games) < 1)
            <p>Jelenleg nincsenek nyitott játékok.</p>
            <a class="btn btn-lg btn-primary" href="{{ route('games.create') }}">Új játék létrehozása</a>
        @else
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Várakozó játékos</th>
                    <th scope="col">Játék létrehozva ekkor</th>
                    <th scope="col">Műveletek</th>
                </tr>
                </thead>
                <tbody>
                    @foreach ($waiting_games as $waiting_game)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{ $waiting_game->createdBy->name }}</td>
                            <td>{{ $waiting_game->created_at }}</td>
                            <td>
                                <form action="{{ route('games.join', ['id' => $waiting_game->id]) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-success">Csatlakozás</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif
</div>
@endsection

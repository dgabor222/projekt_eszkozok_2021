@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Statisztikák</h1>
    @if(!isset($users) || empty($users) || $users->count() < 1)
        <p>Nem található statisztika</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Név</th>
                    <th scope="col">Lejátszott játékok</th>
                    <th scope="col">Megnyert játékok</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->games->count() }}</td>
                        <td>{{ $user->winnedGames->count() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $users->links() }}
    @endif
</div>
@endsection

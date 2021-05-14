@extends('layouts.app')

@section('content')
<div class="container">
    <div id="game" data-ended="{{ $game->status === 'WINNED' || $game->status === 'ENDED' }}" data-symbol="{{ $game->id }}" data-game-id="{{ $game->id }}"></div>
</div>
@endsection

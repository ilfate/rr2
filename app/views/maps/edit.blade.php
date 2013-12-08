@extends('layouts.scaffold')

@section('main')

<a href="/map/create" class="btn btn-info" >Create new Map</a>

<h1>Edit map {{{ $map->name }}}</h1>

@if ($errors->any())
<ul>
    {{ implode('', $errors->all('<li class="error">:message</li>')) }}
</ul>
@endif

{{ Form::open(array('method' => 'POST', 'url' => array('map/save/' . $map->id))) }}
<ul>
    <li>
        {{ Form::label('name', 'Name:') }}
        {{ Form::text('name', $map->name) }}
    </li>
    <li>
        {{ Form::label('width', 'Width (in chunks):') }}
        {{ Form::text('width', $map->width) }}
        {{ Form::label('height', 'Height (in chunks):') }}
        {{ Form::text('height', $map->height) }}
    </li>
    <li>
        {{ Form::label('chunk_size', 'Chunk size:') }}
        {{ Form::text('chunk_size', $map->chunk_size) }}
    </li>
    <li>
        {{ Form::label('max_players', 'Maximum players:') }}
        {{ Form::text('max_players', $map->max_players) }}
    </li>
    <li>
        {{ Form::label('is_pvp', 'is pvp:') }}
        {{ Form::text('is_pvp', $map->is_pvp) }}
    </li>
    <li>
        {{ Form::label('type', 'Type:') }}
        {{ Form::select('type', array($possibleTypes), $map->type) }}
    </li>
    <li>
        {{ Form::label('spawn', 'Spawn:') }}
        {{ Form::select('spawn', array($possibleSpawns), $map->spawn) }}
    </li>
    <li>
        {{ Form::label('bioms[]', 'Bioms:') }}
        {{ Form::select('bioms[]', array($possibleBioms), $map->getBioms(), array('multiple' => 'multiple')) }}
    </li>

    <li>
        {{ Form::submit('Create', array('class' => 'btn btn-info')) }}
    </li>
</ul>
{{ Form::close() }}


@stop

@extends('layouts.scaffold')

@section('main')

@if ($errors->any())
<ul>
    {{ implode('', $errors->all('<li class="error">:message</li>')) }}
</ul>
@endif

{{ Form::open(array('method' => 'POST', 'url' => array('map/create'))) }}
<ul>
    <li>
        {{ Form::label('name', 'Name:') }}
        {{ Form::text('name') }}
    </li>
    <li>
        {{ Form::label('width', 'Width (in chunks):') }}
        {{ Form::text('width') }}
        {{ Form::label('height', 'Height (in chunks):') }}
        {{ Form::text('height') }}
    </li>
    <li>
        {{ Form::label('chunk_size', 'Chunk size:') }}
        {{ Form::text('chunk_size', 10) }}
    </li>
    <li>
        {{ Form::label('max_players', 'Maximum players:') }}
        {{ Form::text('max_players', 100) }}
    </li>
    <li>
        {{ Form::label('is_pvp', 'is pvp:') }}
        {{ Form::text('is_pvp', 0) }}
    </li>
    <li>
        {{ Form::label('type', 'Type:') }}
        {{ Form::select('type', array($possibleTypes), 'normal') }}
    </li>
    <li>
        {{ Form::label('spawn', 'Spawn:') }}
        {{ Form::select('spawn', array($possibleSpawns), 'random') }}
    </li>
    <li>
        {{ Form::label('bioms[]', 'Bioms:') }}
        {{ Form::select('bioms[]', array($possibleBioms), '', array('multiple' => 'multiple')) }}
    </li>

    <li>
        {{ Form::submit('Create', array('class' => 'btn btn-info')) }}
    </li>
</ul>


{{ Form::close() }}


@stop

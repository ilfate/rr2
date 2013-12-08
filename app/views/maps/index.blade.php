@extends('layouts.scaffold')

@section('main')

<a href="/map/create" class="btn btn-info" >Create new Map</a>

<h1>All Maps</h1>

@if (count($maps))
	<table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>Id</th>
            <th>Name (type)</th>
            <th>Size | Chunk size</th>
            <th>Spawn</th>
            <th>Max Players | Game mode</th>
            <th>Bioms</th>
            <th>Active</th>
            <th>Battle maps</th>
        </tr>
        </thead>
		<tbody>
			@foreach ($maps as $map)
				<tr>
					<td>{{{ $map->id }}}</td>
					<td><a href="/map/edit/{{{ $map->id }}}" 'class'='btn btn-info' >
                        {{{ $map->name }}}
                    </a> ({{{ $map->type }}})</td>
                    <td>{{{ $map->width . ' X ' . $map->height }}} | {{{ $map->chunk_size }}}</td>
                    <td>{{{ $map->spawn }}}</td>
                    <td>{{{ $map->max_players }}} | {{{ $map->is_pvp ? 'PvP' : 'PvE' }}}</td>
                    <td>{{{ $map->bioms }}}</td>
                    <td>{{{ $map->active ? 'active' : 'disabled' }}}</td>
                    <td>
                        @if (isset($battleMaps[$map->id]))
                            @foreach ($battleMaps[$map->id] as $battleMap)
                                <a href="/battle-map/show/{{{ $battleMap->id}}} ">{{{ $battleMap->id }}}</a>
                            @endforeach
                        @endif
                    </td>
                    <td>
                        @if ($map->active)
                            {{ Form::open(array('method' => 'POST', 'url' => array('battle-map/create/' . $map->id))) }}
                            {{ Form::submit('Create battle map', array('class' => 'btn btn-info')) }}
                            {{ Form::close() }}
                        @else
                            Only active map is possible for creating battle maps
                        @endif
                    </td>
                    <td>
                        {{ Form::open(array('method' => 'POST', 'url' => array('map/delete/' . $map->id))) }}
                            {{ Form::submit('Delete', array('class' => 'btn btn-danger')) }}
                        {{ Form::close() }}
                    </td>
				</tr>
			@endforeach
		</tbody>
	</table>
@else
	There are no maps!!!
@endif

@stop

@extends('layouts.main')

@section('content')

@if (count($wizards))
<table class="table table-striped table-bordered">
    <thead>
    <tr>
        <th>Wizards</th>
    </tr>
    </thead>

    <tbody>
    @foreach ($wizards as $wizard)
    <tr>
        <td>
            <a href="/wizard/overview/{{{ $wizard->id }}}" 'class'='btn btn-info' >
            {{{ $wizard->name }}}
            </a>
        </td>
        <td>{{{ $wizard->level }}}</td>
        <td>
            @if ($availableBattleMaps)
                @foreach ($availableBattleMaps as $map)
                    {{ Form::open(array('method' => 'POST', 'url' => array('wizard/portal/' . $map->id . '/' . $wizard->id))) }}
                    {{ Form::submit('Go to map ' . $map->id, array('class' => 'btn btn-info')) }}
                    {{ Form::close() }}
                @endforeach
            @endif
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
@else
There are no wizards
@endif

@stop
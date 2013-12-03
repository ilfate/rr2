@extends('layouts.main')

@section('content')

{{ Form::open(array('url' => 'home/create-wizard')) }}
<table class="table table-striped table-bordered">
    <tbody>
    @foreach ($wizardsConfig as $wizard)
    <tr>
        <td>

        </td>
    </tr>
    @endforeach
    </tbody>
</table>
{{ Form::label('name', 'Name:') }}
{{ Form::text('name') }}
{{ Form::submit('Submit', array('class' => 'btn btn-info')) }}
{{ Form::close() }}

@stop
@extends('layouts.main')

@section('content')

{{ Form::open(array('url' => 'wizard/create-wizard')) }}
<table class="table table-striped table-bordered">
    <tbody>
    @foreach ($wizardsConfig as $type => $wizard)
    <tr>
        <td>
            {{ Form::radio('type', $type) }}
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
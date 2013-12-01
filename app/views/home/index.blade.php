@extends('layouts.main')

@section('content')

@if (count($wizards))
<table class="table table-striped table-bordered">
    <thead>
    <tr>
        <th>Role</th>
    </tr>
    </thead>

    <tbody>
    @foreach ($wizards as $wizard)
    <tr>
        <td>{{{ $wizard->name }}}</td>
        <td>{{{ $wizard->level }}}</td>
    </tr>
    @endforeach
    </tbody>
</table>
@else
There are no wizards
@endif

@stop
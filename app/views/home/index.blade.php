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
    </tr>
    @endforeach
    </tbody>
</table>
@else
There are no wizards
@endif

@stop
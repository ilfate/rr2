@extends('layouts.scaffold')

@section('main')


<table class="table table-striped table-bordered">
    <tr>
        <td>
            @if ($battleMap->active)
            This map is active
            @else
            This map is <span style="color:red">inactive</span>
            @endif
        </td>
        <td>
            {{{ $countChunks }}} chunks created
            <a href="/battle-map/full-generate/{{{ $battleMap->id }}}"> Generate all</a>
        </td>
    </tr>

</table>

@stop

@extends('layouts.main')

@section('content')
        @if (Auth::check())
            <b>{{{ Auth::user()->username }}}</b>
            <a href="/home">Go to Home page</a>
        @else
            {{ link_to_route('login.index', 'Login') }}
        @endif
        <h1>You have arrived.</h1>
@stop
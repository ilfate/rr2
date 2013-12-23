@extends('layouts.main')

@section('content')

<div class="page-header">
    <h1>Games <small>Some of my games</small></h1>
</div>

<div class="row show-grid">

    <div class="span8" >

        <canvas id="canvas" width="576" height="576">
            alternate content
        </canvas>

        <script>
            CanvasActions.init(<?= $gameData ?>, <?= $gameLog ?>)
        </script>

    </div>
</div>

@stop
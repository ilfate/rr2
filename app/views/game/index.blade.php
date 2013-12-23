@extends('layouts.main')

@section('content')

<div class="row show-grid">

    <div class="span8" >

        <canvas class="canvas" id="canvasMap">
            alternate content
        </canvas>
        <canvas class="canvas" id="canvasUnits">
            alternate content
        </canvas>

        <script>
            CanvasActions.init(<?= $gameData ?>, <?= $gameLog ?>)
        </script>

    </div>
</div>

@stop
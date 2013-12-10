@extends('layouts.main')

@section('body')


<table class="table table-striped table-bordered">
    <tr>
        <td>
            @if ($battleMap['active'])
            This map is active
            @else
            This map is <span style="color:red">inactive</span>
            @endif
        </td>
        <td>
            {{{ $countChunks }}} chunks created
            @if (!$countChunks)
            <a href="/battle-map/full-generate/{{{ $battleMap['id'] }}}"> Generate all</a>
            @endif
        </td>
    </tr>
    <tr>
        <td>
            @if (!empty($grid))

            <div class="row show-grid">

                <div class="span8 map-grid" >
                    <? /*
  <? for ($i = $square-1; $i >= $startX; $i--) { ?>
    <? for ($i2 = $startX; $i2 < $square; $i2++) { ?>

      <div class="cell cell-<?=$map[$i2][$i]?>"></div>
    <? } ?>
      <div class="clear" ></div>
  <? } ?>
*/?>

                    <? for ($i = $rangeY-1; $i >= $startY; $i--) { ?>
                        <? for ($i2 = $startX; $i2 < $rangeX; $i2++) { ?>

                            <div class="biom biom-<?=$grid[$i2][$i]['biom_id']?>">
                                <? $chunkCellsX = ($i2 - 1) * $battleMap['chunk_size'] ?>
                                <? $chunkCellsY = ($i - 1) * $battleMap['chunk_size'] ?>
                                <? for ($y = $battleMap['chunk_size']; $y > 0; $y--) { ?>
                                    <? for ($x = 1; $x <= $battleMap['chunk_size']; $x++) { ?>
                                        <div class="cell cell-<?=$grid[$i2][$i]['cellsParsed'][$chunkCellsX + $x][$chunkCellsY + $y]?>"></div >
                                    <? } ?>
                                <? } ?>
                            </div>
                        <? } ?>
                        <div class="clear" ></div>
                    <? } ?>
                </div>





            </div>

            @endif


        </td>
    </tr>

</table>

@stop

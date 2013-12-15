<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2013
 */


namespace Game\Units\Logic;


use Game\Game;
use Game\Map\MapObject;
use Game\Units\Actions\Action;
use Game\Units\Unit;

abstract class Logic {

    /**
     * @param Unit $unit
     * @param Game $game
     *
     * @internal param \Game\Map\MapObject $map
     * @return Action
     */
    static public function decision(Unit $unit, Game $game)
    {

    }
} 